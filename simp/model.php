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
        if (!isset($this->_composites))
        {
            $this->_composites = array();
        }
        $this->_composites[$model_name] = $autoload;
    }

    // An aggregate is a 1-many association with models created
    // by outside the aggregating model
    public function AddAggregate($model_name, $autoload = false)
    {
        if (!isset($this->_aggregates))
        {
            $this->_aggregates = array();
        }
        $this->_aggregates[$model_name] = $autoload;
    }

    public function UpdateFromArray($vars)
    {
        global $log;
        foreach ($vars as $name => $val)
        {
            $log->logDebug("updating field: " . $name);
            if ($this->IsChild($name) && is_array($val))
            {
                //$log->logDebug(print_r($_POST, true));
                $var_name = Pluralize(SnakeCase($name));
                $assoc_key = SnakeCase($this) . "_id";
                if (property_exists($this, $var_name))
                {
                    foreach ($val as $id => $fields)
                    {
                        $log->logDebug("updating $name:$id with " . print_r($fields, true));
                        if ($id > 0)
                        {
                            $this->$var_name[$id]->UpdateFromArray($val[$id]);
                        }
                        else
                        {
                            $log->logDebug("creating new child of $name with assoc_key $assoc_key $this->id");
                            $child = \simp\DB::Instance()->Create(ClassCase($name));
                            $log->logDebug("adding index $id to $var_name");
                            $child->UpdateFromArray($val[$id]);
                            $children = &$this->$var_name;
                            $children[] = $child;
                        }
                    }
                }
                else
                {
                    // TODO: log this
                    $log->logWarning("$var_name is not a property of " . get_class($this));
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

        if (count($this->_composites) > 0) $this->load_children($this->_composites);
        if (count($this->_aggregates) > 0) $this->load_children($this->_aggregates);
    }
    
    protected function load_children(&$children)
    {
        global $log;
        foreach ($children as $name => $autoload)
        {
            if ($autoload)
            {
                $var_name = Pluralize(SnakeCase($name));
                $assoc_key = SnakeCase($this) . "_id";
                $log->logDebug("should have $var_name as array");
                if (property_exists($this, $var_name))
                {
                    $log->logDebug(get_class($this) . "->" . $var_name . " exists.");
                    $this->$var_name = \simp\DB::Instance()->Find($name, "$assoc_key = ?", array($this->id));
                    if (!$this->$var_name) $this->$var_name = array();
                    $log->logDebug("$var_name: " . print_r($this->$var_name, true));
                }
                else
                {
                    // TODO: add exception or something to notify
                    $log->logDebug("Property " . get_class($this) . "->{$var_name} does not exist.");
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

    protected function save_children(&$children)
    {
        global $log;
        foreach ($children as $name => $autoload)
        {
            if ($autoload)
            {
                $var_name = Pluralize(SnakeCase($name));
                $assoc_key = SnakeCase($this) . "_id";
                //$log->logDebug("$var_name: " . print_r($this->$var_name, true));
                foreach ($this->$var_name as $child) 
                {
                    $child->$assoc_key = $this->id;
                    $log->logDebug("saving child:" . print_r($child, true));
                    \simp\DB::Instance()->Save($child);
                }
            }
        }
    }

    public function after_update()
    {
        global $log;
        $log->logDebug("in " . get_class($this) . "::after_update()");

        if (count($this->_composites) > 0) {$log->logDebug("saving composites"); $this->save_children($this->_composites);}
        if (count($this->_aggregates) > 0) {$log->logDebug("saving aggregates"); $this->save_children($this->_aggregates);}

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
    
    public function __get($name)
    {
        if (property_exists($this, $name))
        {
            global $log;
            $log->logDebug("getting $name");
            return $this->$name;
        }
        else
        {
            return parent::__get($name);
        }
    }
}
