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
}
