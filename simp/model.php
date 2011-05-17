<?
namespace simp;
require_once "rb.php";

//load_database("sqlite:db/development.db");

class Model 
{
    protected $_bean;
    protected $_table_name;
    protected $_errors;
    protected $_associations;

    public static function LoadModel($classname)
    {
        global $log;
        global $APP_BASE_PATH;
        $filename = SnakeCase($classname) . ".php";
        $path = $APP_BASE_PATH . "/models/" . $filename;
        $module_path = $APP_BASE_PATH . "/modules/";
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
        $this->_associations = array();
    }

    public function __toString()
    {
        return get_class($this);
    }

    static public function TableName($model_name)
    {
        return SnakeCase($model_name);
    }

    public function ManyToMany($assoc)
    {
        $this->_associations[Pluralize($assoc)] = array();
    }

    //**** Bean wrapping stuff
    // Create a new model
    static public function Create($model_name)
    {
        global $log;
        return new $model_name(\R::dispense(Model::TableName($model_name)));
    }

    static public function FindOrCreate($model_name, $conditions, $values)
    {
        $model = NULL;
        if ($model = Model::FindOne($model_name, $conditions, $values) == NULL)
        {
            $model = Create($model_name);
        }
        return $model;
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

    static public function FindAll($model_name, $order = "")
    {
        return Model::Find($model_name, "1 " . $order, array());
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

    
    public function Bean()
    {
        return $this->_bean;
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

    protected function FindAssociated($child, $args = NULL, $vals = NULL)
    {
        $models = array();
        $model_name = Singularize($child);
        $table_name = SnakeCase($model_name);
        $class_name = ClassCase($model_name);
        $beans = \R::related($this->_bean, $table_name, $args, $vals);
        // load model for each
        foreach ($beans as $id => $bean)
        {
            $models[$id] = new $class_name($bean);
        }
        return $models;
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
        else if (array_key_exists($property, $this->_associations))
        {
            if (count($this->_associations[$property]) < 1)
            {
                // find associated
                $this->_associations[$property] = $this->FindAssociated($property);
            }
            return $this->_associations[$property];
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
        /*
        else if (array_key_exists($property, $this->_associations))
        {
            $this->_associations[$property] = $value;
        }
        */
        else
            $this->_bean->$property = $value;
    }

    public function __call($name, $args)
    {
        list($action, $assoc) = explode("_", SnakeCase($name));
        // get all of the associated models, possible constraing by args
        if (array_key_exists($assoc, $this->_associations))
        {
            switch($action)
            {
            case "Get":
                return $this->$assoc;
                break;
            case "Find":
                return $this->FindAssociated($assoc, $args[0], $args[1]);
                break;
            }
        }
        // one of the associated models
        else if (array_key_exists(Singularize($assoc), $this->_associations))
        {
            switch($action)
            {
            case "Add":
                $model = $args[0];
                if (!array_key_exists($model->id))
                {
                    \R::associate($this->Bean(), $model->Bean());
                    \RedBean_Plugin_Constraint::addConstraint($this->Bean(), $model->Bean());
                }
                $this->_associations[$assoc][$model->id] = $model; 
                break;
            }
        }
    }

    public function UpdateFromArray($vars)
    {
        global $log;
        foreach ($vars as $name => $val)
        {
            if ($name != "id") $this->$name = $val;
        }
        // update associations
        foreach ($this->_associations as $assoc)
        {
            $assoc->Save();
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

    // verification methods
    protected function HasErrors()
    {
        return count($this->_errors > 0);
    }

    protected function VerifyMaxLength($field, $length, $errmsg = NULL)
    {
        $ok = true;
        if (strlen($this->$field) > $length)
        {
            $msg = $errmsg == NULL ? "{$field} must be no longer than {$length} characters." : $msg;
            $this->SetError($field, $msg);
            $ok = false;
        }
        return $ok;
    }

    protected function VerifyMinLength($field, $length, $errmsg = NULL)
    {
        $ok = true;
        if (strlen($this->$field) < $length)
        {
            $msg = $errmsg == NULL ? "{$field} must be at least {$length} characters." : $msg;
            $this->SetError($field, $msg);
            $ok = false;
        }
        return $ok;
    }

    protected function VerifyValidDate($field)
    {
        // valid dates are mm/dd/yyyy or mm-dd-yyyy
        $ok = true;
        if (preg_match('~(\d{1,2})[/:.,_\-\' ](\d{1,2})[/:.,_\-\' ](\d{4})~', $this->$field, $match))
        {
            if (checkdate($match[1], $match[2], $match[3]) == false)
            {
                $ok = false;
            }
        }
        else $ok = false;

        if ($ok == false)
        {
            $msg = "{$field} must be a valid date (mm/dd/yyyy)";
            $this->setError($field, $msg);
        }
    }
}
