<?php
class Field extends \simp\Model
{
    const THREEVTHREE = 1;
    const FOURVFOUR = 2;
    const SIXVSIX = 3;
    const EIGHTVEIGHT = 4;
    const ELEVENVELEVEN = 5;

    public static $formats = array(
        self::THREEVTHREE => '3 v 3 (U5, U6)',
        self::FOURVFOUR => '4 v 4 (U7, U8)',
        self::SIXVSIX => '6 v 6 (U9, U10)',
        self::EIGHTVEIGHT => '8 v 8 (Academy, U11, U12rec)',
        self::ELEVENVELEVEN => '11 v 11 (Academy, U12comp, U13-U18)',
    );

    protected $_complex;
    protected $_blackouts;

    public $divisions;

    public function Setup()
    {
        //$open = array();
        //$close = array();
        $this->divisions = array();
        $this->_complex = FALSE;
        $this->_blackouts = array();
    }

    public function OnLoad()
    {
        $div_beans = \R::related($this->_bean, 'division');
        foreach ($div_beans as $id => $bean)
        {
            $this->divisions[$id] = new Division($bean);
        }
    }

    public function BeforeDelete()
    {
        \R::clearRelations($this->_bean, 'division');
        return true;
    }

    public function AddDivision($div)
    {
        if (!array_key_exists($div->id, $this->divisions))
        {
            \R::associate($this->_bean, $div->Bean());
            $this->divisions[$div->id] = $div;
        }
    }

    public function __get($property)
    {
        switch($property)
        {
        case 'complex':
            if (!$this->_complex && $this->id > 0)
            {
                $this->_complex = \simp\Model::FindById("Complex", $this->complex_id);
            }
            return $this->_complex;
        case 'blackouts':
            if (empty($this->_blackouts) && $this->id > 0)
            {
                $this->_blackouts = \simp\Model::Find("Blackout", "field_id = ?", array($this->id));
            }
            return $this->_blackouts;
        default:
            return parent::__get($property);
        }
    }

    public function BeforeSave()
    {
        return $this->HasErrors() == false;
    }

    public function GetAvailability($date, $times)
    {
        //$fields = \simp\Model::Find('Field', 'format = ?', 

        $avail = array();
        //$times = GenerateTimeSlots($start_time, $end_time, 30);

        $matches = \simp\Model::Find(
            "Match", 
            "date = ? and field_id = ?",
            array($date, $this->id)
        );

        $blackout = \simp\Model::FindOne("Blackout", "field_id = ? and date = ?", array($this->id, $date));
        
        // what is dow?
        $dow = strftime("%u", $date);
        $open = strtotime($this->complex->open[$dow]);
        $close = strtotime($this->complex->close[$dow]);


        foreach ($times as $t)
        {
            $avail[$t] = 'closed';
            //echo "$time c Comparing $t >= $open && $t <= $close";
            if ($t >= $open && $t < $close)
            {
                $avail[$t] = 'open';
            }
            if ($blackout)
            {
                $bs = strtotime($blackout->start_time);
                $be = strtotime($blackout->end_time);

                if ($t >= $bs && $t < $be)
                {
                    $avail[$t] = 'black';
                }
            }
        }

        foreach ($matches as $match)
        {
            $ms = strtotime($match->start_time);
            $me = strtotime($match->end_time);
            foreach ($times as $t)
            {
                //echo "m comparing $t >= $ms && $t < $me";
                if ($t >= $ms && $t < $me)
                {
                    $avail[$t] = "game";
                }
            }
        }

        return $avail;
    }

    public static function DivisionOpts()
    {
        $q = "select distinct division, age, gender from match order by division, age, gender;";
        $divisions = \R::getAll($q);
        $opts = array();
        foreach ($divisions as $div)
        {
            $did = implode(':', $div);
            $opts[$did] = implode(' ', $div);
        }
        return $opts;
    }

}
