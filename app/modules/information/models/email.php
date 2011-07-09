<?php
require_once "category.php";
class Email extends \simp\Model
{

    // fields
    // - address
    // - category_id
    // - param

    public static $genders = array(0 => 'boys and girls', 1 => 'boys', 2 => 'girls');
    public static $ages = array(
        4 => 'u5', 5 => 'u6', 6 => 'u7', 7 => 'u8', 8 => 'u9',
        9 => 'u10', 10 => 'u11', 11 => 'u12', 12 => 'u13', 13 => 'u14',
        14 => 'u15', 15 => 'u16', 16 => 'u17', 17 => 'u18');

    public static $end_ages = array(
        0 => 'only',
        4 => 'to u5', 5 => 'to u6', 6 => 'to u7', 7 => 'to u8', 8 => 'to u9',
        9 => 'to u10', 10 => 'to u11', 11 => 'to u12', 12 => 'to u13', 13 => 'to u14',
        14 => 'to u15', 15 => 'to u16', 16 => 'to u17', 17 => 'to u18');

    public $data;
    public $datum_label;
    public $datum_type;
    public $datum_required;
    public $datum_options;
    public function Setup()
    {
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
            $data = \simp\Model::Find("Datum", "parent_type = ? and parent_id = ?", array("Email", $this->id));
            foreach ($data as $datum) $this->data[$datum->id] = $datum;
        }

    }

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
                $datum->parent_type = "Email";
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
            $datum->parent_type = "Email";
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
