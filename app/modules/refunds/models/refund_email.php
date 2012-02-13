<?php
class RefundEmail extends \simp\Model
{

    public function BeforeSave()
    {
        $this->VerifyEmail('email');
        $this->VerifyNotEmpty('name');
        return ($this->HasErrors() == false);
    }
}
