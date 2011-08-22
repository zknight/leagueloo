<?php
class Match extends \simp\Model
{
    public function Setup()
    {
    }

    public function BeforeSave()
    {
        //print_r($this);
        return true;
    }
}
