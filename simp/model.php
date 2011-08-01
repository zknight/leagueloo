<?
namespace simp;
require_once "rb.php";

//load_database("sqlite:db/development.db");
class Model extends BaseModel
{
    protected $_bean;
    protected $_table_name;
    protected $_associations;

    public static function LoadDatabase($dbspec, $user, $password)
    {
        static $DB_loaded = false;
        if (!$DB_loaded) \R::setup($dbspec, $user, $password);
        $DB_loaded = true;
    }

    public function __construct($bean = NULL)
    {
        parent::__construct();
        $this->_associations = array();
        $this->_table_name = Model::TableName($this->__toString());
        $this->_bean = $bean;
        if (method_exists($this, "Setup"))
        {
            $this->Setup();
        }
    }

    static public function TableName($model_name)
    {
        return SnakeCase($model_name);
    }

    public function ManyToMany($assoc)
    {
        $this->_associations[Pluralize($assoc)] = array();
        global $log; $log->logDebug("ManyToMany assocs: $this " . print_r($this->_associations, true));
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
        $model = Model::FindOne($model_name, $conditions, $values);
        if ($model == NULL)
        {
            $model = Model::Create($model_name);
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

    static public function FromBean($model_name, $bean)
    {
        $model = NULL;
        if ($bean)
        {
            $model = new $model_name($bean);
            $model->OnLoad();
        }
        return $model;
    }

    static public function Count($model_name, $conditions = 1, $values = array())
    {
        $name = Model::TableName($model_name);
        $count = \R::getCell(
            "select count(*) from {$name} where {$conditions};",
            $values);

        return $count;
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
        $first_save = false;
        if ($this->id == 0)
        {
            $first_save = true;
        }
        if ($this->BeforeSave())
        {
            // save associations
            foreach ($this->_associations as $name => $assocs)
            {
                foreach ($assocs as $assoc)
                {
                    $assoc->Save();
                }
            }
            if ($this->_bean)
            {
                try
                {
                    $id = \R::store($this->_bean);
                    if ($first_save)
                    {
                        $this->AfterFirstSave();
                    }
                    else
                    {
                        $this->AfterSave();
                    }
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
        if ($property == "Days")
        {
            $log->logDebug(print_r($this->_associations, true));
        }
        if (property_exists($this, $property))
        {
            $log->logDebug("__get: is property.");
            return $this->$property;
        }
        else if (array_key_exists($property, $this->_associations))
        {
            $log->logDebug("$property is associated model.");
            if (count($this->_associations[$property]) < 1)
            {
                // find associated
                $this->_associations[$property] = $this->FindAssociated($property);
            }
            $log->logDebug("$property: " . print_r($this->_associations[$property], true));
            return $this->_associations[$property];
        }
        else
        {
            if (!isset($this->_bean)) $log->logDebug("Holy crap!");
            $log->logDebug("Getting $this -> $property as bean");
            return $this->_bean->$property;
        }
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
        global $log; $log->logDebug("__call: $name");
        $parts = explode("_", SnakeCase($name));
        $action = array_shift($parts);
        $assoc = ClassCase(implode("_", $parts));
        $log->logDebug("__call: action = $action, assoc = $assoc");
        // get all of the associated models, possible constraing by args
        if (array_key_exists($assoc, $this->_associations))
        {
            switch($action)
            {
            case "get":
                return $this->$assoc;
                break;
            case "find":
                return $this->FindAssociated($assoc, $args[0], $args[1]);
                break;
            }
        }
        // one of the associated models
        $assoc = Pluralize($assoc);

        if (array_key_exists($assoc, $this->_associations))
        {
            switch($action)
            {
            case "add":
                $model = $args[0];
                $log->logDebug("Adding $assoc");
                if (!array_key_exists(($model->id), $this->_associations[$assoc]))
                {
                    \R::associate($this->Bean(), $model->Bean());
                    \RedBean_Plugin_Constraint::addConstraint($this->Bean(), $model->Bean());
                }
                $this->_associations[$assoc][$model->id] = $model; 
                break;
            }
        }
    }
}
