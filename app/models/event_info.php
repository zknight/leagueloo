<?
/// EventInfo model
/// 
/// Stores information about an individual event
///
/// Information stored (columns)
///     title
///     description
///     location
///     start_date
///     end_date
///     start_time
///     end_time
///     repeat_type 0:none 1:daily 2:weekly 3:monthly 4:annually
///     day_mask
///     week_of_month
///     day_of_week => computed from start_date
///     day_of_month => computed from start_date
///     monthly_repeat_type 0:day_of_week 1:day_number
///     repeat_interval 
///     all_day
class EventInfo extends \simp\Model
{
    // pseudo fields
    public $start_time_str;
    public $end_time_str;
    public $start_date_str;
    public $end_date_str;

    protected $day_mask;

    protected static $ord = array(
        1 => 'first', 
        2 => 'second', 
        3 => 'third', 
        4 => 'fourth', 
        5 => 'fifth');
    protected static $day_map = array(
        'sunday', 
        'monday', 
        'tuesday', 
        'wednesday', 
        'thursday', 
        'friday');
    protected static $month_map = array(
        'Jan', 'Feb', 'Mar', 'Apr', 
        'May', 'Jun', 'Jul', 'Aug', 
        'Sep', 'Oct', 'Nov', 'Dec');
    protected static $repeat_type_tok = array(
        1 => 'D', 2 => 'W', 3 => 'M', 4 => 'Y');

    public function Setup()
    {
        global $log; 
        $log->logDebug("EventInfo::Setup() this " . print_r($this, true));
        $this->day_mask = explode(",", $this->days_of_week);
        if ($this->id > 0)
        {
            $log->logDebug("EventInfo::Setup() day_mask = " . print_r($this->day_mask, true));
            $log->logDebug("EventInfo::Setup() days_of_week = " . $this->days_of_week);
            $this->start_time_str = strftime("%I:%M %p", $this->start_time);
            $this->end_time_str = strftime("%I:%M %p", $this->end_time);
            $this->start_date_str = strftime("%m/%d/%Y", $this->start_date);
            $this->end_date_str = strftime("%m/%d/%Y", $this->end_date);
        }
    }

    public static function GetEvents($start_date, $end_date)
    {
        // query that stuff
        $events = array();
        \R::debug(true);
        $event_array = self::Find(
            "EventInfo", 
            "start_date <= ? and end_date >= ?", 
            array($end_date->getTimestamp(), $start_date->getTimestamp()));
        \R::debug(false);

        foreach ($event_array as $ev_info)
        {
            switch ($ev_info->repeat_type)
            {
            case 0:
                $ev_info->repeat_interval = 1;
                // intentional fall-through
            case 1: // daily
                $ev_info->GetDailyEvents($start_date, $end_date, $events);
                break;
            case 2:
                $ev_info->GetWeeklyEvents($start_date, $end_date, $events);
                break;
            case 3:
                $ev_info->GetMonthlyEvents($start_date, $end_date, $events);
                break;
            case 4:
                $ev_info->GetAnnualEvents($start_date, $end_date, $events);
                break;
            }
        }
        return $events;
    }

    public function AfterUpdate()
    {
        $this->start_date = strtotime($this->start_date_str);
        $this->ComputeEndDate();
    }

    public function BeforeSave()
    {
        if ($this->repeat_type == 2)
        {
            if ($this->VerifyArraySize(
                'day_mask',
                7, 
                "Something is wrong with the application. " . 
                "Please contact the system administrator. " . 
                "(day_mask is wrong count)"))
            {
                $this->days_of_week = implode(",", $this->day_mask);
            }
        }
        $this->VerifyMaxLength('short_title', 32); 
        $this->VerifyMinLength('short_title', 3);
        $this->VerifyMinLength('title', 3);
        $this->VerifyMinLength('location', 3);
        $this->VerifyValidDate('start_date_str');
        $this->VerifyValidDate('end_date_str');

        $start_date = new \DateTime();
        $start_date->setTimestamp($this->start_date);
        $this->dow = ceil($start_date->format("j") / 7); 
        $this->wom = $start_date->format("w");
        $this->dom = $start_date->format("j");

        //$this->ComputeEndDate();

        if (!$this->all_day)
        {
            $this->start_time = strtotime($this->start_time_str);
            if (!$this->start_time) $this->SetError('start_time_str', "Start Time is invalid");
            $this->end_time = strtotime($this->end_time_str);
            if (!$this->end_time) $this->SetError('end_time_str', "End Time is invalid");

            if ($this->start_time > $this->end_time)
            {
                $this->SetError('end_time_str', "End Time must be later than Start Time");
            }
        }

        return ($this->HasErrors() == false);
    }

    protected function ComputeEndDate()
    {
        global $log; $log->logDebug("ComputeEndDate: repeat_end = {$this->repeat_end}");
        if ($this->repeat_end == "occurrences")
        {
            $type = EventInfo::$repeat_type_tok[$this->repeat_type];
            $end_date = new \DateTime();
            $end_date->setTimestamp($this->start_date);
            $count = ($this->recur_count - 1) * $this->repeat_interval;
            $log->logDebug("type = $type");
            $end_date->add(new \DateInterval("P{$count}{$type}"));
            $this->end_date = $end_date->getTimestamp();
            $this->end_date_str = strftime("%m/%d/%Y", $this->end_date);
        }
        else
        {
            $this->end_date = strtotime($this->end_date_str);
        }
    }

    protected function GetDailyEvents($start, $end, &$events)
    {
        //$this->ComputeEndDate();
        $day_date = new \DateTime();
        $day_date->setTimestamp($this->start_date);

        while ($day_date->getTimestamp() <= $this->end_date && 
               $day_date->getTimestamp() <= $end->getTimestamp())
        {
            $key = $day_date->getTimestamp();
            $check = clone $day_date;
            $day_date->add(new \DateInterval("P{$this->repeat_interval}D"));
            if ($check->getTimestamp() < $start->getTimestamp()) continue;

            if (!array_key_exists($key, $events)) $events[$key] = array();
            $events[$key][] = new Event($this);
        }
    }

    protected function GetWeeklyEvents($start, $end, &$events)
    {
        //$this->ComputeEndDate();
        $week_date = new \DateTime();
        $week_date->setTimestamp($this->start_date);
        while ($week_date->getTimestamp() <= $this->end_date && 
               $week_date->getTimestamp() <= $end->getTimestamp())
        {
            $day_date = clone $week_date;
            $week_date->add(new \DateInterval("P{$this->repeat_interval}W"));
            if ($day_date->getTimestamp() < $start->getTimestamp()) continue;

            for ($i = 0; $i < 7; $i++)
            {
                $dow = $day_date->format("w");
                global $log; $log->logDebug("GetWeeklyEvents:checking date :" . $day_date->format("w m/d/Y"));
                if ($this->day_mask[$dow] == 1)
                {
                    $log->logDebug("    IS IN!");
                    $key = $day_date->getTimestamp();
                    if (!array_key_exists($key, $events)) $events[$key] = array();
                    $events[$key][] = new Event($this);
                }
                $day_date->add(new \DateInterval("P1D"));
            }
        }
    }

    protected function GetMonthlyEvents($start, $end, &$events)
    {
        //$this->ComputeEndDate();
        $month_date = new \DateTime();
        $month_date->setTimestamp($this->start_date);
        while ($month_date->getTimestamp() <= $this->end_date && 
               $month_date->getTimestamp() <= $end->getTimestamp())
        {
            $day_date = clone $month_date;
            $month_date->add(new \DateInterval("P{$this->repeat_interval}M"));
            if ($day_date->getTimestamp() < $this->start_date) continue;

            if ($this->repeat_by == "dow") // dow
            {
                $wom = EventInfo::$ord[$this->wom];
                $dow = EventInfo::$day_map[$this->dow];
                $month = $day_date->format("F Y");
                $dt = new \DateTime("$wom $dow of $month");
                $key = $dt->getTimestamp();
                if (!array_key_exists($key, $events)) $events[$key] = array();
                $events[$key][] = new Event($this);
            }
            else if ($this->repeat_by == "dom") // dom
            {
                list($m, $y) = explode("/", $day_date->format("m/Y"));
                $dt = new \DateTime("{$m}/{$this->dom}/{$y}");
                $key = $dt->getTimestamp();
                global $log; $log->logDebug("GetMonthlyEvents(): timestamp $key on $m/{$this->dom}/$y");
                if (!array_key_exists($key, $events)) $events[$key] = array();
                $events[$key][] = new Event($this);
            }
        }
    }

    protected function GetAnnualEvents($start, $end, &$events)
    {
        //$this->ComputeEndDate();
        $year_date = clone $start;
        while ($year_date->getTimestamp() <= $this->end_date && 
               $year_date->getTimestamp() <= $end->getTimestamp())
        {
            $day_date = clone $year_date;
            $year_date->add(new \DateInterval("P{$this->repeat_interval}Y"));
            if ($year_date->getTimestamp() < $this->start_date) continue;

            $doy = date("z", $this->start_date);
            $y = $day_date->format("Y");
            $dt = \DateTime::createFromFormat("z Y", "$doy $y");
            $key = $dt->getTimestamp();
            if (!array_key_exists($key, $events)) $events[$key] = array();
            $events[$key][] = new Event($this);
        }
    }

    public function __get($property)
    {
        switch($property)
        {
        case "day_mask":
            return $this->day_mask;
            break;
        default:
            $foo = parent::__get($property);
            return $foo;
            break;
        }
    }

    public function __set($property, $value)
    {
        switch($property)
        {
        case "day_mask":
            if (is_array($value))
            {
                $this->day_mask = $value;
            }
            else if (is_string($value))
            {
                // check for error in BeforeSave()
                $this->day_mask = explode(",", $value);
            }
            break;
        default:
            parent::__set($property, $value);
            break;
        }
    }
}


/// contains event information created from EventInfo
class Event
{
    public $title;
    public $description;
    public $location;
    public $start_time;
    public $end_time;

    public function __construct($event_info)
    {
        $this->title = $event_info->title;
        $this->description = $event_info->description;
        $this->location = $event_info->location;
        $this->start_time = $event_info->start_time;
        $this->end_time = $event_info->end_time;
        $this->category = $event_info->entity_name;
    }

}
