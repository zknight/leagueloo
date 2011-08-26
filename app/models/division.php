<?php

class Division extends \simp\Model
{
    protected $_fields;
    public function Setup()
    {
        $this->_fields = array();
    }

    public function OnLoad()
    {
    }

    public function __get($property)
    {
        switch($property)
        {
        case "fields": 
            if (empty($this->_fields) && $this->id > 0)
            {
                $f_beans = \R::related($this->_bean, 'field');
                foreach ($f_beans as $id => $bean)
                {
                    $this->_fields[$id] = new Field($bean);
                }
            }
            return $this->_fields;
            break;
        default:
            return parent::__get($property);
        }
    }

}
