<?php
class Notice extends \simp\Model
{
    protected $_pub_date;
    protected $_exp_date;

    public function Setup()
    {
    }

    public function OnLoad()
    {
        $this->_pub_date = FormatDateTime($this->start_date, "m/d/Y");
        $this->_exp_date = FormatDateTime($this->end_date, "m/d/Y");
    }

    public function BeforeSave()
    {
        $errors = 0;
        if (strlen($this->name) > 40)
        {
            $errors++;
            $this->SetError('name', "Name must be less than 40 characters.");
        }

        if (strlen($this->text) > 80)
        {
            $errors++;
            $this->SetError('text', "Text must be less than 80 characters.");
        }

        if (!$this->VerifyValidDate('publish_date', $this->_pub_date)) $errors++;
        if (!$this->VerifyValidDate('expire_date', $this->_exp_date)) $errors++;

        //echo "ERROR COUNT = $errors\n";
        if ($errors == 0)
        {
            $dt = new \DateTime("{$this->_pub_date}");
            $this->start_date = $dt->getTimeStamp();
            $dt = new \DateTime("{$this->_exp_date}");
            $this->end_date = $dt->getTimeStamp();
        }

        return $errors == 0;
    }

    public function __get($property)
    {
        switch ($property)
        {
        case 'publish_date':
            return $this->_pub_date;
            break;
        case 'expire_date':
            return $this->_exp_date;
            break;
        default:
            return parent::__get($property);
            break;
        }
    }

    public function __set($property, $value)
    {
        switch ($property)
        {
        case 'publish_date':
            $this->_pub_date = $value;
            break;
        case 'expire_date':
            $this->_exp_date =$value;
            break;
        default:
            parent::__set($property, $value);
            break;
        }
    }

}
