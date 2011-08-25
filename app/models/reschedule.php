<?php
class Reschedule extends \simp\Model
{
    const PENDING = 0;
    const APPROVED = 1;
    const DENIED = 2;

    const MORNING = 1;
    const AFTERNOON = 2;

    public static $tod_opts = array(
        MORNING => "Morning (before Noon)",
        AFTERNOON => "Afternoon (prior to 7:00p)",
    );

    protected $_divisions;
    public $step;
    public $orig_date_str;
    public $first_choice_str;
    public $second_choice_str;

    public function Setup()
    {
    }

    public function OnLoad()
    {
        $this->orig_date_str = FormatDateTime($this->orig_date, "m/d/Y");
        $this->first_choice_str = FormatDateTime($this->first_choice, "m/d/Y");
        $this->second_choice_str = FormatDateTime($this->second_choice, "m/d/Y");
    }

    public function __get($property)
    {
        switch ($property)
        {
        case 'divisions':
            if (empty($this->_divisions))
            {
                $q = "select distinct division, age, gender from match order by division, age, gender;";
                $this->_divisions = \R::getAll($q);
            }
            return $this->_divisions;
            break;
        case 'division_spec':
            return $this->DivisionOpts();
            break;
        default:
            return parent::__get($property);
        }
    }

    public function __set($property, $val)
    {
        switch($property)
        {
        case 'division_spec':
            list($this->division, $this->age, $this->gender) = explode(":", $val);
            break;
        default:
            parent::__set($property, $val);
            break;
        }
    }

    public function DivisionOpts()
    {
        $divs = $this->divisions;
        $opts = array();
        foreach ($divs as $div)
        {
            $did = implode(':', $div);
            $opts[$did] = implode(' ', $div);
        }
        return $opts;
    }

    public function BeforeSave()
    {
        if ($this->step == 1)
        {
            $this->VerifyEmail('opponent_email');
            $this->VerifyNotEmpty('opponent_name');
            $this->VerifyValidDate('orig_date_str');
            $this->orig_date = strtotime($this->orig_date_str);
        }
        if ($this->step == 2)
        {
            $this->VerifyNotEmpty('match_id');
            $this->VerifyNotEqual('match_id', 0);
            $this->VerifyValidDate('first_choice_str');
            $this->VerifyValidDate('second_choice_str');
            $this->first_choice = strtotime($this->first_choice_str);
            $this->second_choice = strtotime($this->second_choice_str);
        }

        return !$this->HasErrors();
    }
}
