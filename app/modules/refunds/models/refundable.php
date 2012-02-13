<?php
class Refundable extends \simp\Model
{
    // expiry
    // name
    //
    public $expiry_str;

    public function Setup()
    {
    }

    public function OnLoad()
    {
        if ($this->id > 0)
        {
            $this->expiry_str = strftime("%m/%d/%Y", $this->expiry);
        }
    }

    public function BeforeSave()
    {
        $this->VerifyValidDate('expiry_str');
        $this->VerifyMinLength('name', 3);
        $this->expiry = strtotime($this->expiry_str);
        return ($this->HasErrors() == false);
    }
}
