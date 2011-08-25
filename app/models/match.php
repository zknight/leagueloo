<?php
class Match extends \simp\Model
{
    public $date_str;

    public function Setup()
    {
    }

    public function OnLoad()
    {
        $this->date_str = FormatDateTime($this->date, "m/d/Y");
    }

    public function BeforeSave()
    {
        //print_r($this);
        $this->VerifyDateFormat("date_str", $this->date_str);
        $this->date = strtotime($this->date_str);
        return !$this->HasErrors();
    }
}
