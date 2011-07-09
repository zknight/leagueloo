<?php
require_once "datum.php";
class Category extends \simp\Model
{
    // fields
    // - name
    // - text
    // - classification

    public $emails;
    const NONE = 0;
    const AGE = 1;
    const CUSTOM = 2;

    public $data;

    public static $check_types = array(
        self::NONE => "none",
        self::AGE => "age",
        self::CUSTOM => "custom"
    );

    public function Setup()
    {
        $this->emails = array();
        $this->data = array();
    }

    public function OnLoad()
    {
        if ($this->id > 0) 
        {
            $this->emails = \simp\Model::Find("Email", "category_id = ?", array($this->id));
            $data = \simp\Model::Find("Datum", "category_id = ?", array($this->id));
            foreach ($data as $datum) $this->data[$datum->id] = $datum;
        }

    }

    public function __set($property, $value)
    {
        if ($property == 'datum_id')
        {
            if (!is_array($value)) $value = array($value);
            foreach ($value as $id)
            {
                $this->data[$id] = \simp\Model::FindById("Datum", $id);
            }
        }
        else
        {
            parent::__set($property, $value);
        }
    }

    public function AfterFirstSave()
    {
        foreach ($this->data as $datum)
        {
            $datum->category_id = $this->id;
            $datum->Save();
        }
    }

}
