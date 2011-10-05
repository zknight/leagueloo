<?php
class Game extends \simp\Model
{
    public $date_str;
    protected $_field;

    public function Setup()
    {
        $this->_field = null;
    }

    public function OnLoad()
    {
        $this->date_str = FormatDateTime($this->date, "m/d/Y");
    }

    public function __get($property)
    {
        switch($property)
        {
        case "field":
            if (!isset($this->_field) && $this->id > 0)
            {
                $this->_field = \simp\Model::FindById("Field", $this->field_id);
            }
            return $this->_field;
            break;
        default:
            return parent::__get($property);
        }
    }

    public function BeforeSave()
    {
        //print_r($this);
        $this->VerifyDateFormat("date_str", $this->date_str);
        $this->date = strtotime($this->date_str);
        $this->updated_at = time();
        return !$this->HasErrors();
    }
}
