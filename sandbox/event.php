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

    protected static $ord = array(1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth', 5 => 'fifth');
    protected static $day_map = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday');
    protected static $month_map = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    protected static $events = array();

    public function __construct(
        $text,
        $start_date, 
        $end_date,
        $repeat_type = 0,
        $repeat_interval = 1,
        $recur_count = 0,
        $days_of_week = array(),
        //$dow = 0,
        //$dom = 0,
        $repeat_by = 0
        )
    {
        $this->text = $text;
        $this->start_date = $start_date->getTimestamp();
        $this->end_date = $end_date->getTimestamp();
        $this->recur_count = $recur_count;
        $this->repeat_type = $repeat_type;
        $this->days_of_week = $days_of_week;
        $this->dow = array(ceil($start_date->format("j") / 7), $start_date->format("w"));
        $this->dom = $start_date->format("j");
        $this->repeat_by = $repeat_by;
        $this->repeat_interval = $repeat_interval;

        self::$events[] = $this;
        echo "created $text with start {$this->start_date} and end {$this->end_date}\n";
    }

    public static function GetEvents($start, $end)
    {
        //echo "have " . count(self::$events) . " events\n";
        $events = array();
        foreach (self::$events as $event)
        {
            //echo "$event->repeat_type\n";
            switch ($event->repeat_type)
            {
            case 1: // daily
                $event->GetDailyEvents($start, $end, $events);
                //echo "event count now: " . count($events) . "\n";
                break;
            case 2:
                $event->GetWeeklyEvents($start, $end, $events);
                break;
            case 3:
                $event->GetMonthlyEvents($start, $end, $events);
                break;
            case 4:
                $event->GetAnnualEvents($start, $end, $events);
                break;
            }
        }
        //echo "done. \n";
        return $events;
    }

    protected function ComputeEndDate($type)
    {
        if ($this->recur_count > 0)
        {
            $end_date = new DateTime();
            $end_date->setTimestamp($this->start_date);
            $count = ($this->recur_count - 1) * $this->repeat_interval;
            $end_date->add(new DateInterval("P{$count}{$type}"));
            $this->end_date = $end_date->getTimestamp();
        }
    }

    protected function GetDailyEvents($start, $end, &$events)
    {
        // check to see if we should recalculate end date
        echo "start date is: " . strftime("%m/%d/%y", $this->start_date);
        $this->ComputeEndDate("D");
        $day_date = new DateTime();
        $day_date->setTimestamp($this->start_date);

        while ($day_date->getTimestamp() <= $this->end_date && $day_date->getTimestamp() <= $end->getTimestamp())
        {
            $key = $day_date->getTimestamp();
            $check = clone $day_date;
            $day_date->add(new DateInterval("P{$this->repeat_interval}D"));
            if ($check->getTimestamp() < $start->getTimestamp()) continue;

            if (!array_key_exists($key, $events)) $events[$key] = array();
            $events[$key][] = $this->text;
        }
    }

    protected function GetWeeklyEvents($start, $end, &$events)
    {
        $this->ComputeEndDate("W");
        //$week_date = clone $start;
        $week_date = new DateTime();
        $week_date->setTimestamp($this->start_date);
        while ($week_date->getTimestamp() <= $this->end_date && $week_date->getTimestamp() <= $end->getTimestamp())
        {
            $day_date = clone $week_date;
            $week_date->add(new DateInterval("P{$this->repeat_interval}W"));
            if ($day_date->getTimestamp() < $start->getTimestamp()) continue;

            for ($i = 0; $i < 7; $i++)
            {
                $dow = $day_date->format("w");
                if ($this->days_of_week[$dow] == 1)
                {
                    $key = $day_date->getTimestamp();
                    if (!array_key_exists($key, $events)) $events[$key] = array();
                    $events[$key][] = $this->text;
                }
                $day_date->add(new DateInterval("P1D"));
            }
        }
    }


    protected function GetMonthlyEvents($start, $end, &$events)
    {
        $this->ComputeEndDate("W");
        $month_date = new DateTime();
        $month_date->setTimestamp($this->start_date);
        while ($month_date->getTimestamp() <= $this->end_date && $month_date->getTimestamp() <= $end->getTimestamp())
        {
            $day_date = clone $month_date;
            $month_date->add(new DateInterval("P{$this->repeat_interval}M"));
            if ($day_date->getTimestamp() < $this->start_date) continue;

            if ($this->repeat_by == 0) // dow
            {
                $wom = Event::$ord[$this->dow[0]];
                $dow = Event::$day_map[$this->dow[1]];
                $month = $day_date->format("F Y");
                $dt = new DateTime("$wom $dow of $month");
                $key = $dt->getTimestamp();
                if (!array_key_exists($key, $events)) $events[$key] = array();
                $events[$key][] = $this->text;
            }
            else if ($this->repeat_by == 1) // dom
            {
                list($m, $y) = explode("/", $day_date->format("m/Y"));
                $dt = new DateTime("{$m}/{$this->dom}/{$y}");
                $key = $dt->getTimestamp();
                if (!array_key_exists($key, $events)) $events[$key] = array();
                $events[$key][] = $this->text;
            }
        }
    }

    protected function GetAnnualEvents($start, $end, &$events)
    {
        $this->ComputeEndDate("W");
        $year_date = clone $start;
        while ($year_date->getTimestamp() <= $this->end_date && $year_date->getTimestamp() <= $end->getTimestamp())
        {
            $day_date = clone $year_date;
            $year_date->add(new DateInterval("P{$this->repeat_interval}Y"));
            if ($year_date->getTimestamp() < $this->start_date) continue;

            $doy = date("z", $this->start_date);
            $y = $day_date->format("Y");
            $dt = DateTime::createFromFormat("z Y", "$doy $y");
            $key = $dt->getTimestamp();
            if (!array_key_exists($key, $events)) $events[$key] = array();
            $events[$key][] = $this->text;
        }
    }

        
}
/*
new Event(
    "every day", 
    new DateTime("5/19/2011"), 
    new DateTime("7/11/2011"),
    1,
    1,
    5);

new Event(
    "every other day", 
    new DateTime("5/19/2011"), 
    new DateTime("7/11/2011"),
    1,
    2,
    5);

new Event(
    "every third day",
    new DateTime("5/19/2011"), 
    new DateTime("7/11/2011"),
    1,
    3,
    3);

new Event(
    "Every Tuesday/Thursday",
    new DateTime("5/22/2011"),
    new DateTime("12/31/2037"),
    2,
    1,
    6,
    array(0, 0, 1, 0, 1, 0, 0)
    );

new Event(
    "Every other Wednesday",
    new DateTime("5/10/2011"),
    new DateTime("8/10/2011"),
    2,
    2,
    25,
    array(0, 0, 0, 1, 0, 0, 0)
    );
*/

new Event(
    "Every third monday",
    new DateTime("5/16/2011"),
    new DateTime("12/31/2037"),
    3,
    1,
    0,
    array(),
    0);

new Event(
    "The 16th of every other month",
    new DateTime("5/16/2011"),
    new DateTime("12/31/2037"),
    3,
    2,
    0,
    array(),
    1);
/*

new Event(
    "May 16th of every year",
    new DateTime("5/16/2011"),
    new DateTime("12/31/2016"),
    4,
    1);

*/
$events = Event::GetEvents(new DateTime("5/1/2011"), new DateTime("9/30/2018"));
ksort($events);
//echo "events count is " . count($events) . "\n";
foreach ($events as $ts=>$ev)
{
    echo strftime("%a %m/%d/%y", $ts) . ":\n";
    foreach ($ev as $e)
    {
        echo "\t$e\n";
    }
}
?>
</pre>
