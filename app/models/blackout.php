<?php

class Blackout extends \simp\Model
{
    public $date_str; 

    public function Setup()
    {
    }

    public function OnLoad()
    {
        if ($this->date) $this->date_str = FormatDateTime($this->date, "m/d/Y");
        //if ($this->start) $this->start_time = FormatDateTime($this->start, "g:i a");
        //if ($this->end) $this->end_time = FormatDateTime($this->end, "g:i a");
    }

    public function BeforeSave()
    {
        $this->VerifyDateFormat("date_str", $this->date_str);  
        $this->VerifyTimeFormat("start_time", $this->start_time);
        $this->VerifyTimeFormat("end_time", $this->end_time);
        if (!$this->HasErrors())
        {
            $this->date = strtotime($this->date_str);
        }
        return !$this->HasErrors();
    }

}
