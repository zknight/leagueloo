<?php
require_once "money.php";
class Txn extends \simp\Model
{
    const DEBIT = 1;
    const CREDIT = 2;
    public static $types = array(
        self::DEBIT => "Debit",
        self::CREDIT => "Credit"
    );

    public function Setup()
    {
    }

    public function OnLoad()
    {
        $this->amount = ToDollars($this->value);
    }

    public function BeforeSave()
    {
        $amt = FromDollars($this->amount);
        $this->value = $this->type == self::CREDIT ? $amt : -$amt;
        $this->VerifyValidDate("date");
        $this->VerifyNotEmpty("description");
        $this->VerifyNotEmpty("amount");
        return !$this->HasErrors();
    }


    public function __get($property)
    {
        switch ($property)
        {
        case "date":
            return strftime("%m/%d/%Y", $this->timestamp);
            break;
        default:
            return parent::__get($property);
        }
    }

    public function __set($property, $value)
    {
        switch ($property)
        {
        case "date":
            $this->timestamp = strtotime($value);
            break;
        default:
            parent::__set($property, $value);
            break;
        }
    }


}
