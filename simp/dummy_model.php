<?php
namespace simp;

class DummyModel extends BaseModel
{
    protected $properties;    

    public function __construct()
    {
        parent::__construct();
        $this->properties = array();
    }

    public function __get($prop)
    {
        if (array_key_exists($prop, $this->properties))
        {
            return $this->properties[$prop];
        }
        return NULL;
    }

    public function __set($prop, $val)
    {
        global $log;
        $log->logDebug("DummyModel: setting $prop to $val");
        $this->properties[$prop] = $val;
    }

}
