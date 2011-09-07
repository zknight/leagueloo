<?php
class FormElement extends \simp\DummyModel
{
    const DATE = 1;
    const TEXT = 2;
    const TIME = 3;
    const EMAIL = 4;

    protected $_fields;

    public function __construct($fields = array())
    {
        parent::__construct();
        $this->_fields = $fields;
    }

    public function BeforeSave()
    {
        foreach ($this->properties as $key => $value)
        {
            if (isset($this->_fields[$key]))
            {
                switch ($this->_fields[$key])
                {
                case self::DATE:
                    $this->VerifyNotEmpty($key);
                    $this->VerifyDateFormat($key, $value);
                    break;
                case self::TEXT:
                    $this->VerifyNotEmpty($key);
                    break;
                case self::TIME:
                    $this->VerifyNotEmpty($key);
                    $this->VerifyTimeFormat($key, $value);
                    break;
                case self::EMAIL:
                    $this->VerifyEmail($key);
                    break;
                default:
                    break;
                }
            }
        }
        return !$this->HasErrors();
    }
}
