<pre>
<?
class Event
{
    protected $text;
    protected $start_date;
    protected $end_date;
    protected $recur_count;
    protected $repeat_type; // 0 => none, 1 => daily, 2 => monthly, 3 => yearly
    protected $days_of_week;
    protected $dow;
    protected $dom;
    protected $repeat_by; // 0 => dow, 1 => dom
    protected $repeat_interval;

    protected static $events = array();

    public function __construct(
        $text,
        $start_date, 
        $end_date,
        $repeat_type = 0,
        $repeat_interval = 1,
        $recur_count = 0,
        $days_of_week = array(),
        $dow = 0,
        $dom = 0,
        $repeat_by = 0
        )
    {
        $this->text = text;
        $this->start_date = $start_date->getTimestamp();
        $this->end_date = $end_date->getTimestamp();
        $this->recur_count = $recur_count;
        $this->repeat_type = $repeat_type;
        $this->days_of_week = $days_of_week;
        $this->dow = $dow;
        $this->dom = $dom;
        $this->repeat_by = $repeat_by;
        $this->repeat_interval = $repeat_interval;
        self::$events[] = $this;
        echo "created $text with start {$this->start_date} and end {$this->end_date}\n";
    }

    public static function GetEvents($start, $end)
    {
        echo "have " . count(self::$events) . " events\n";
        $events = array();
        foreach (self::$events as $event)
        {
            echo "$event->repeat_type\n";
            switch ($event->repeat_type)
            {
            case 1: // daily
                array_merge($events, $event->GetDailyEvents($start, $end));
                break;
            }
        }
        echo "done. \n";
        return $events;
    }

    protected function GetDailyEvents($start, $end)
    {
        echo "GetDailyEvents\n";
        $events = array();
        $day_date = clone $start;

        echo "starting day_date: " . $day_date->format("m/d/Y") . "\n";
        echo "this->end_date: " . strftime("%m/%d/%Y", $this->end_date) . "\n";
        echo "end: " . $end->format("m/d/Y") . "\n";
        echo "comparing " .  $day_date->getTimestamp() . " <= " . $this->end_date . " && " . $day_date->getTimestamp() . " <= " . $end->getTimestamp() . "\n";
        while ($day_date->getTimestamp() <= $this->end_date && $day_date->getTimestamp() <= $end->getTimestamp())
        {
            $events[$day_date->getTimestamp()] = $this->text;
            $day_date->add(new DateInterval("P{$this->repeat_interval}D"));
            echo "day_date is now: " . $day_date->format("m/d/Y") . "\n";
        }
        echo "returning.\n";
        return $events;
    }
}

new Event(
    "every day", 
    new DateTime("5/19/2011"), 
    new DateTime("7/11/2011"),
    1,
    1);

new Event(
    "every other day", 
    new DateTime("5/19/2011"), 
    new DateTime("7/11/2011"),
    1,
    2);

new Event(
    "every third day",
    new DateTime("5/19/2011"), 
    new DateTime("7/11/2011"),
    1,
    3);

$events = Event::GetEvents(new DateTime("5/1/2011"), new DateTime("6/15/2011"));
echo "events count is " . count($events);
foreach ($events as $ts=>$ev)
{
    echo strftime("%m/%d/%y", $ts) . ": $ev\n";
}
?>
</pre>
