<?php
class InfoRequest extends \simp\DummyModel
{ 

    public $fields;
    public $field_info;
    public function Setup()
    {
        $this->field_info = array();
        $this->fields = array();
    }

    public function AddFields($data)
    {
        foreach ($data as $datum)
        {
            $this->field_info[$datum->id] = array(
                'type' => $datum->type,
                'label' => $datum->label, 
                'required' => $datum->required,
                'opt' => $datum->options);
        }
    }

    public function BeforeSave()
    {
        return false;
    }

}
