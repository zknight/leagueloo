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
/// derived fields:
///     all_day:
///     repeat_interval: weekly, monthly, yearly
///     day_mask: days/weeks on which to repeat
class Event extends \simp\Model
{
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
}
