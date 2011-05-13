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
            break;
        case 'repeat_weekly':
            $this->repeat_daily = false;
            $this->repeat_weekly = $value;
            $this->repeat_monthly = false;
            $this->repeat_annually = false;
            break;
        case 'repeat_monthly':
            $this->repeat_daily = false;
            $this->repeat_weekly = false;
            $this->repeat_monthly = $value;
            $this->repeat_annually = false;
            break;
        case 'repeat_annually':
            $this->repeat_daily = false;
            $this->repeat_weekly = false;
            $this->repeat_monthly = false;
            $this->repeat_annually = $value;
            break;
        default:
            return parent::__set($property, $value);
        }
    }

}
