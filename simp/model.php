<?
namespace simp;
class Model extends \RedBean_SimpleModel
{
    protected $_composites;
    protected $_aggregates;

    public function __construct()
    {
        if (method_exists($this, "Setup"))
        {
            $this->Setup();
        }
    }

    public function __toString()
    {
        return get_class($this);
    }

    // A composite is a 1-many association with models that are
    // created by the composing model
    public function AddComposite($model_name, $autoload = false)
    {
        if (!$this->_composites)
        {
            $this->_composites = array();
        }
        $this->_composites[$model] = $autoload;
    }

    // An aggregate is a 1-many association with models created
    // by outside the aggregating model
    public function AddAggregate($model, $autoload = false)
    {
        if (!$this->_aggregates)
        {
            $this->_aggregates = array();
        }
        $this->_aggregates[$model] = $autoload;
    }

    public function UpdateFromArray($vars)
    {
        foreach ($vars as $name => $val)
        {
            if ($this->IsChild($name) && is_array($val))
            {
                $var_name = Pluralize(SnakeCase($name));
                if (property_exists($this, $var_name))
                {
                    //$this->$var_name = \simp\DB::Instance()->Find($name, 'user_id = ?', array($this->id));
                }
                else
                {
                    // TODO: add exception or something to notify
                    $log->logDebug("Property " . get_class($this) . "->{$var_name} does not exist.");
                }       
            }
            else
            {
                $this->$name = $val;
            }
        }
    }

    protected function IsChild($value)
    {
        return $this->IsAggregate($value) || $this->IsComposite($value);
    }

    protected function IsAggregate($value)
    {
        return $this->_aggregates && array_key_exists($value, $this->_aggregates);
    }

    protected function IsComposite($value)
    {
        return $this->_composites && array_key_exists($value, $this->_composites);
    }

    //// Event CALLBACKS
    // callback for when model is created
    public function dispense()
    {
        global $log;
        $log->logDebug("in " . get_class($this) . "::dispense()");
    }

    // callback for when model is opened (find, load)
    public function open()
    {
        global $log;
        $log->logDebug("in " . get_class($this) . "::open() with id {$this->id}");

        if ($this->_composites)
        {
            foreach ($this->_composites as $name => $autoload)
            {
                if ($autoload)
                {
                    $var_name = Pluralize(SnakeCase($name));
                    if (property_exists($this, $var_name))
                    {
                        $this->$var_name = \simp\DB::Instance()->Find($name, 'user_id = ?', array($this->id));
                    }
                    else
                    {
                        // TODO: add exception or something to notify
                        $log->logDebug("Property " . get_class($this) . "->{$var_name} does not exist.");
                    }
                }
            }
        }
    }

    // callback for when model is saved
    public function update()
    {
        global $log;
        $log->logDebug("in " . get_class($this) . "::update()");
        if (method_exists($this, "OnSave"))
        {
            $this->OnSave();
        }
    }

    public function after_update()
    {
        global $log;
        $log->logDebug("in " . get_class($this) . "::after_update()");
        if (method_exists($this, "AfterSave"))
        {
            $this->AfterSave();
        }
    }

    public function delete()
    {
        global $log;
        $log->logDebug("in " . get_class($this) . "::delete({$this->id})");
        if (method_exists($this, "OnDelete"))
        {
            $this->OnDelete();
        }
        if ($this->_composites)
        {
            foreach (array_keys($this->_composites) as $class_name) 
            {
                $log->logDebug("attempting to delete $class_name where type = " .
                    get_class($this) . " and entity = " . $this->id);
                $models = \simp\DB::Instance()->Find(
                    $class_name, 
                    "type=? and entity=?",
                    array(get_class($this), $this->id));
                $log->logDebug("found models: " . print_r($models, true));
                foreach ($models as $model)
                {
                    \simp\DB::Instance()->Delete($model);
                }
            }
        }

    }

    public function after_delete()
    {
        global $log;
        $log->logDebug("in " . get_class($this) . "::after_delete()");
        if (method_exists($this, "AfterDelete"))
        {
            $this->AfterDelete();
        }
    }
}
