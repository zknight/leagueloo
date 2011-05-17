<?
/// Event model
///
/// An event belongs to a program or team
/// fields:
///     short_title: short title used in url?
///     title: event title
///     description: description of event
///     location: location of event
///     start_date:
///     end_date:
///     start_time:
///     end_time:
///     entity_type
///     entity_id
///     entity_name
///     all_day:
/// derived fields:
///     repeat_interval: weekly, monthly, yearly
///     day_mask: days/weeks on which to repeat
class Event extends \simp\Model
{
    protected $repeat_daily;
    protected $repeat_weekly;
    protected $repeat_monthly;
    protected $repeat_annually;
    protected $start_date;
    protected $end_date;
    //protected $repeat_interval;
    //protected $repeat_end;
    //protected $repeat_by;
    //protected $day_mask;

    public function Setup()
    {
        $this->ManyToMany("Day");
    }
    // lookups
    //
    // by start date
    //   - before
    //   - after
    // by end date
    //   - before
    //   - after
    // between start and end (current event?)
    //
    // cur_date < start_date -> upcoming
    // cur_date > end_date -> expired
    // cur_date >= start_date and cur_date <= end_date -> current
    public static function FindUpcoming($date, $entity_type = NULL, $entity_id = NULL)
    {
        $params = "start_date > ?";
        $values = array($date);
        return Event::FindWithExpr($params, $values, $entity_type, $entity_name);
    }

    public static function FindExpired($date, $entity_type = NULL, $entity_id = NULL)
    {
        $params = "end_date < ?";
        $values = array($date);
        return Event::FindWithExpr($params, $values, $entity_type, $entity_name);
    }

    public static function FindCurrent($date, $entity_type = NULL, $entity_id = NULL)
    {
        $params = "start_date <= ? and end_date >= ?";
        $values = array($date, $date);
        return Event::FindWithExpr($params, $values, $entity_type, $entity_name);
    }

    protected static function FindWithExpr($expr, $values, $entity_type, $entity_name)
    {
        if (isset($entity_type))
        {
            $expr .= " and entity_type = ?";
            $values[] = $entity_type;
        }
        if (isset($entity_id))
        {
            $expr .= " and entity_id = ?";
            $values[] = $entity_id;
        }

        return Event::Find("event", $expr, $values);
    }

    public function __get($property)
    {
        switch($property)
        {
        case 'repeat_daily':
            return $this->repeat_daily;
            break;
        case 'repeat_weekly':
            return $this->repeat_weekly;
            break;
        case 'repeat_monthly':
            return $this->repeat_monthly;
            break;
        case 'repeat_annually':
            return $this->repeat_annually;
            break;
        case 'day_mask':
            global $log; $log->logDebug("getting day mask: " . print_r($this->days_of_week, true));
            return explode(",", $this->days_of_week);
            break;
        case 'start_date':
            $this->GetStartDate();
            return $this->start_date;
            break;
        case 'end_date':
            $this->GetEndDate();
            return $this->end_date;
            break;
        default:
            return parent::__get($property);
        }
    }

    public function __set($property, $value)
    {
        switch($property)
        {
        case 'repeat_daily':
            $this->repeat_daily = $value;
            $this->repeat_weekly = false;
            $this->repeat_monthly = false;
            $this->repeat_annually = false;
            $this->repeat_type = '1';
            break;
        case 'repeat_weekly':
            $this->repeat_daily = false;
            $this->repeat_weekly = $value;
            $this->repeat_monthly = false;
            $this->repeat_annually = false;
            $this->repeat_type = '2';
            break;
        case 'repeat_monthly':
            $this->repeat_daily = false;
            $this->repeat_weekly = false;
            $this->repeat_monthly = $value;
            $this->repeat_annually = false;
            $this->repeat_type = '3';
            break;
        case 'repeat_annually':
            $this->repeat_daily = false;
            $this->repeat_weekly = false;
            $this->repeat_monthly = false;
            $this->repeat_annually = $value;
            $this->repeat_type = '4';
            break;
        case 'day_mask':
            $this->days_of_week = implode(",", $value);
            global $log; $log->logDebug("setting day mask: " . print_r($this->days_of_week, true));
            break;
        case 'start_date':
            $this->start_date = $value;
            break;
        default:
            return parent::__set($property, $value);
        }
    }

    public function BeforeSave()
    {
        $this->VerifyMaxLength('short_title', 32); 
        $this->VerifyMinLength('short_title', 3);
        $this->VerifyMinLength('title', 3);
        $this->VerifyMinLength('location', 3);
        if ($this->VerifyValidDate('start_date') && $this->VerifyValidDate('end_date'))
        {
            $this->UpdateEventDates();
        }

        return ($this->HasErrors() == false);
    }

    protected function GetStartDate()
    {
        $dt = \DateTime("12/31/2036");
        $date = $dt->getTimestamp();
        foreach ($this->days as $day)
        {
            if ($day->date <= $date)
            {
                $this->start_date = FormatDateTime($day->date, "m/d/Y");
                $date = $day->date;
            }
        }
    }

    protected function GetEndDate()
    {
        $dt = \DateTime("1/1/1970");
        $date = $dt->getTimestamp();
        foreach ($this->days as $day)
        {
            if ($day->date > $date)
            {
                $this->end_date = FormatDateTime($day->date, "m/d/Y");
                $date = $day->date;
            }
        }
    }

    protected function UpdateEventDates()
    {
        // here's where it gets fun.
        // figure out if there is a repeat and what type it is
        $sdt = \DateTime($this->start_date);
        $edt = \DateTime($this->end_date);

        switch ($this->repeat_type)
        {
        case 1: // daily repeat, look at occurrences and repeat_interval
            if ($this->repeat_end == "occurrences")
            {
                $period = new \DatePeriod

            break;
        case 2: // weekly repeat, look at occurrences, repeat_interval and mask
            break;
        case 3: // monthly repeat, look at occurrences, repeat_interval and repeat_by
            break;
        case 4: // annual repeat, look at occurrences and repeat_interval
            break;
        default: // no repeat
            $period = new \DatePeriod($sdt, new \DateInterval("P1D"), $edt);
            break;
    }

}
