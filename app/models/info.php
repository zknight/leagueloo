<?php
class Info extends \simp\DummyModel
{
    const DATE = 1;
    const TEXT = 2;
    const NUM = 3;

    protected $checked_fields;
    public function __construct($checked_fields = array())
    {
        parent::__construct();
        $this->checked_fields = $checked_fields;
    }

    public function AddCheckedField($field, $type)
    {
        $this->checked_fields[$field] = $type;
    }

    public function BeforeSave()
    {
        foreach ($this->checked_fields as $field => $type)
        {

        }
    }

}
