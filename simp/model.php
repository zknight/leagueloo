<?
namespace simp;
require_once "rb.php";

//load_database("sqlite:db/development.db");

class Model 
{
    protected $_bean;
    protected $_table_name;
    protected $_errors;

    public static function LoadModel($classname)
    {
        global $log;
        global $APP_BASE_PATH;
        $filename = SnakeCase($classname) . ".php";
        $path = $APP_BASE_PATH . "/models/" . $filename;
        //$log->logDebug("attempting to find $classname @ $path");
        if (file_exists($path))
        {
            //echo("attempting to find $classname @ $path");
            require_once $path;
        }
    }

    public static function LoadDatabase($dbspec)
    {
        static $DB_loaded = false;
        if (!$DB_loaded) \R::setup($dbspec);
        $DB_loaded = true;
    }

    public function __construct($bean = NULL)
    {
        if (method_exists($this, "Setup"))
        {
            $this->Setup();
        }
        $this->_table_name = Model::TableName($this->__toString());
        $this->_bean = $bean;
        $this->_errors = array();
    }

    public function __toString()
    {
        return get_class($this);
    }

    static public function TableName($model_name)
    {
        return SnakeCase($model_name);
    }

    //**** Bean wrapping stuff
    // Create a new model
    static public function Create($model_name)
    {
        global $log;
        return new $model_name(\R::dispense(Model::TableName($model_name)));
    }

    static public function FindById($model_name, $id)
    {
        $model = new $model_name;
        if ($model->Load($id))
        {
            $model->OnLoad();
            return $model;
        }
        return NULL;
    }

    static public function Find($model_name, $conditions, $values)
    {
        $models = array(); 
        $beans = \R::find(Model::TableName($model_name), $conditions, $values);
        foreach ($beans as $bean)
        {
            $model = new $model_name($bean);
            $model->OnLoad();
            $models[] = $model;
        }
        return $models;
    }

    static public function FindAll($model_name)
    {
        return Model::Find($model_name, 1, array());
    }

    static public function FindOne($model_name, $conditions, $values)
    {
        $model = NULL;
        $bean = \R::findOne(Model::TableName($model_name), $conditions, $values);
        if ($bean)
        {
            $model = new $model_name($bean);
            $model->OnLoad();
        }
        return $model;
    }

    
    /// Load model by id
    /// @return true
    public function Load($id)
    {
        global $log;
        $this->_bean = \R::load($this->_table_name, $id);
        $this->OnLoad();
        return $this->_bean->id > 0;
    }

    public function Save()
    {
        if ($this->BeforeSave())
        {
            if ($this->_bean)
            {
                try
                {
                    $id = \R::store($this->_bean);
                    $this->AfterSave();
                    return $id;
                }
                catch (RedBean_Exception_Security $e)
                {
                    global $log;
                    $log->logWarning("Error saving {$this->__toString()} with id {$this->id}");
                    return 0;
                }
            }
        }
        return 0;
    }

    public function Delete()
    {
        if ($this->BeforeDelete())
        {
            if ($this->_bean)
            {
                try
                {
                    \R::trash($this->_bean);
                    $this->AfterDelete();
                    return true;
                }
                catch(RedBean_Exception_Security $e)
                {
                    global $log;
                    $log->logWarning("Error deleting {$this->__toString()} with id {$this->id}");
                    return false;
                }

            }
        }
        return false;
    }

    //**** magic!
    public function __get($property)
    {
        global $log;
        $log->logDebug("Getting $this $property\n");
        if (property_exists($this, $property))
        {
            return $this->$property;
        }
        else
            return $this->_bean->$property;
    }

    public function __set($property, $value)
    {
        //echo "Setting $property\n";
        if (property_exists($this, $property))
        {
            $this->$property = $value;
        }
        else
            $this->_bean->$property = $value;
    }

    public function UpdateFromArray($vars)
    {
        global $log;
        foreach ($vars as $name => $val)
        {
            if ($name != "id") $this->$name = $val;
        }
    }

    public function GetErrors()
    {
        return $this->_errors;
    }

    public function SetError($field, $error)
    {
        $this->_errors[$field] = $error;
    }

    //// Event CALLBACKS
    // callback for when model is opened (find, load)
    public function OnLoad()
    {
    }

    public function BeforeSave()
    {
        return true;
    }

    public function AfterSave()
    {
        return true;
    }

    public function BeforeDelete()
    {
        return true;
    }

    public function AfterDelete()
    {
        return true;
    }
}
