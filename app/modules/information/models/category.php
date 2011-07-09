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
    public $datum_label;
    public $datum_type;
    public $datum_required;
    public $datum_options;

    public static $check_types = array(
        self::NONE => "none",
        self::AGE => "age",
        self::CUSTOM => "custom"
    );

    public function Setup()
    {
        $this->emails = array();
        $this->data = array();
        $this->datum_label = array();
        $this->datum_type = array();
        $this->datum_required = array();
        $this->datum_options = array();
    }

    public function OnLoad()
    {
        if ($this->id > 0) 
        {
            $this->emails = \simp\Model::Find("Email", "category_id = ?", array($this->id));
            $data = \simp\Model::Find("Datum", "parent_type = ? and parent_id = ?", array("Category", $this->id));
            foreach ($data as $datum) $this->data[$datum->id] = $datum;
        }

    }

    /*
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
     */

    public function BeforeSave()
    {
        $ok = true;
        foreach ($this->datum_label as $i => $label)
        {
            if ($label)
            {
                $datum = \simp\Model::Create("Datum");
                $datum->label = $label;
                $datum->type = $this->datum_type[$i];
                $datum->required = $this->datum_required[$i];
                $datum->option_string = $this->datum_options[$i];
                $datum->parent_type = "Category";
                if ($this->id > 0) 
                {
                    $datum->parent_id = $this->id;
                    $ok = $datum->Save();
                }
                else
                {
                    $this->data[] = $datum;
                }
                if (!$ok) return false;
            }
        }
        return true;
    }

    public function AfterFirstSave()
    {
        foreach ($this->data as $datum)
        {
            $datum->parent_type = "Category";
            $datum->parent_id = $this->id;
            $datum->Save();
        }
    }

    public function AfterDelete()
    {
        foreach ($this->data as $datum)
        {
            if ($datum->id > 0) $datum->Delete();
        }
    }

}
