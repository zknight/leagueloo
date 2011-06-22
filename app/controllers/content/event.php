<?
namespace app\content;

class EventController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout('content');

        //$this->MapAction("index", "Calendar", \simp\Request::GET);
        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);

        $this->RequireAuthorization(
            array(
                'index',
                'show',
                'add',
                'edit',
                'delete'
            )
        );
    }

    public function Index()
    {
        // load all articles that this user has access to
        $this->StoreLocation();
        $this->user = CurrentUser();
        // TODO: figure out how to 'paginate' a calendar...
        //$this->upcoming_events = $this->user->GetUpcomingEvents();
        //$this->expired_events = $this->user->GetExpiredEvents();
        $dt_now = new \DateTime("now");
        $dt_end = clone $dt_now;
        $dt_end->add(new \DateInterval("P2M"));

        $this->expired_events = \EventInfo::FindExpired(
            $dt_now);
        $this->upcoming_events = \EventInfo::FindEventInfoByDate(
            $dt_now,
            $dt_end);
        return true;
    }

    public function Calendar()
    {
        $this->StoreLocation();
        $this->user = CurrentUser();
        $this->day = NULL;
        $dt = new \DateTime("now");
        $today = FormatDateTime($dt->getTimeStamp(), "d_m_Y");
        list($day, $month, $year) = explode("_", $today);
        global $log; $log->logDebug("Calendar(): " . print_r($this->_params, true));
        if ($this->CheckParam("month") == true && $this->CheckParam("year") == true)
        {
            $m = $this->GetParam("month");
            $y = $this->GetParam("year");
            if ($m == $month || $y == $year)
            {
                $this->day = $day;
            }
            $month = $m; $year = $y;
        }

        $this->cur_date = new \DateTime("$month/1/$year");
        $this->dates = \EventInfo::GetCalendarPeriod($this->cur_date);
        
        return true;
    }

    public function Show()
    {
        $this->StoreLocation();
        return true;
    }

    public function Add()
    {
        $this->user = CurrentUser();
        global $log; $log->logDebug("Event::Add() creating event");
        $this->event_info = \simp\Model::Create('EventInfo');

        if ($this->CheckParam('year') && $this->CheckParam('month') && $this->CheckParam('day'))
        {
            $year = $this->GetParam('year');
            $month = $this->GetParam('month');
            $day = $this->GetParam('day');
            $this->event_info->start_date_str = "{$month}/{$day}/{$year}";
        }

        $this->entities = $this->GetEntities();
        return true;
    }

    public function Create()
    {
        $this->event_info = \simp\Model::Create('EventInfo');
        $this->event_info->UpdateFromArray($this->GetFormVariable("EventInfo"));
        $rerender = $this->CheckSubmitType();

        if ($rerender == true)
        {
            $this->user = CurrentUser();
            $this->entities = $this->GetEntities();
            $this->Render("add");
            return false;
        }

        if (!$this->event_info->Save())
        {
            $this->user = CurrentUser();
            $this->entities = $this->GetEntities();
            $this->Render("add");
            return false;
        }

        if ($this->GetFormVariable("create_article"))
        {
            \Redirect(\Path::content_news_add(
                SnakeCase($this->event_info->entity_type),
                $this->event_info->entity_id,
                "event",
                $this->event_info->id));
        }
        \Redirect(GetReturnURL());

    }

    public function Edit()
    {
        $this->user = CurrentUser();
        $this->event_info = \simp\Model::FindById('EventInfo', $this->GetParam('id'));

        $this->entities = $this->GetEntities();
        return true;
    }

    public function Update()
    {
        $this->event_info = \simp\Model::FindById('EventInfo', $this->GetParam('id'));
        $this->event_info->UpdateFromArray($this->GetFormVariable("EventInfo"));
        $rerender = $this->CheckSubmitType();

        if ($rerender == true)
        {
            $this->user = CurrentUser();
            $this->entities = $this->GetEntities();
            $this->Render("edit");
            return false;
        }

        if (!$this->event_info->Save())
        {
            $this->user = CurrentUser();
            $this->entities = $this->GetEntities();
            $this->Render("edit");
            return false;
        }

        if ($this->GetFormVariable("create_article"))
        {
            \Redirect(\Path::content_news_add(
                SnakeCase($this->event_info->entity_type),
                $this->event_info->entity_id,
                "event",
                $this->event_info->id));
        }

        \Redirect(GetReturnURL());
    }

    public function Remove()
    {
        $id = $this->GetParam('id');
        $event_info = \simp\Model::FindById("EventInfo", $id);
        $title = $event_info->short_title;
        if ($event_info->id > 0)
        {
            $event_info->Delete();
            AddFlash("Event Information $title removed.");
        }
        else
        {
            AddFlash("Invalid Event Information.  Please contact the system administrator.");
        }
        return \Redirect(GetReturnURL());
    }

    // returns true if need to re-render
    protected function CheckSubmitType()
    {
        $rerender = false;
        if ($all_day = $this->GetFormVariable("all_day"))
        {
            $this->event_info->all_day = !$this->event_info->all_day;
            $rerender = true;
        }
        else if ($this->GetFormVariable("repeat_daily"))
        {
            $this->event_info->repeat_type = $this->event_info->repeat_type != 1 ? 1 : 0;
            $rerender = true;
        }
        else if ($this->GetFormVariable("repeat_weekly"))
        {
            $this->event_info->repeat_type = $this->event_info->repeat_type != 2 ? 2 : 0;
            $dt = new \DateTime($this->event_info->start_date_str);
            // TODO: figure out how to set day mask for day automatically when a new event is created
            //$this->event_info->day_mask[$dt->format("w")] = 1;
            $rerender = true;
        }
        else if ($this->GetFormVariable("repeat_monthly"))
        {
            $this->event_info->repeat_type = $this->event_info->repeat_type != 3 ? 3 : 0;
            $rerender = true;
        }
        else if ($this->GetFormVariable("repeat_annually"))
        {
            $this->event_info->repeat_type = $this->event_info->repeat_type != 4 ? 4 : 0;
            $rerender = true;
        }
        return $rerender;
    }

    protected function GetEntities()
    {
        if ($this->CheckParam('entity') && $this->CheckParam('entity_id'))
        {
            $entity = $this->GetParam('entity');
            $id = $this->GetParam('entity_id');
            if ($this->user->CanEdit($entity, $id))
            {
                $entity = \simp\Model::FindById($entity, $id);
                return array("{$entity}:$id" => "{$entity}-{$entity->name}");
            }
            else
            {
                AddFlash("You do not have privileges for that.");
                Redirect(GetReturnURL());
            }
        }
        else
        {
            //$abilities = $this->user->abilities;
            return $this->user->OptionsForEntitiesWithPrivilege("Main,Program,Team", \Ability::EDIT);
        }
    }

    protected function GetCalendarPeriod($date)
    {
        $today = $this->DayFromDate(new \DateTime("now"));
        $start = $this->DayFromDate($date);
        $fdom_dt = new \DateTime("{$start['m']}/1/{$start['y']}");
        $fdom = $this->DayFromDate($fdom_dt);
        $ldom_dt = new \DateTime("{$start['m']}/1/{$start['y']}");
        $ldom_dt->add(new \DateInterval("P1M"));
        $ldom_dt->sub(new \DateInterval("P1D"));
        $ldom = $this->DayFromDate($ldom_dt);
        $span = $ldom['d'] - $fdom['d'] + $fdom['w'];
        $w = 7 - $ldom['w'];
        $span = $span + $w;
        //echo "fdom={$fdom['m']}/{$fdom['d']} ldom={$ldom['m']}/{$ldom['d']}span=$span";
        $fdom_dt->sub(new \DateInterval("P{$fdom['w']}D"));
        $first_day = $this->DayFromDate($fdom_dt);
        $ldom_dt->add(new \DateInterval("P{$w}D"));
        $last_day = $this->DayFromDate($ldom_dt);
        $period = new \DatePeriod($fdom_dt, new \DateInterval("P1D"), $ldom_dt);
        $dates = array();
        $events = \EventInfo::GetEvents($fdom_dt, $ldom_dt);
        global $log; $log->logDebug("GetCalendarPeriod: events " . print_r($events, true));
        $i = 1;
        foreach ($period as $dt)
        {
            $date = $this->DayFromDate($dt, $i);
            $ev_key = $dt->getTimestamp();
            $log->logDebug("$i Date: " . $dt->format("m/d/Y") . " => ev_key: $ev_key");
            $i++;
            // make it array so a foreach won't barf later
            $date['events'] = array_key_exists($ev_key, $events) ? $events[$ev_key] : array();
            if ($date['y'] == $fdom['y'] && $date['m'] == $fdom['m'])
            {
                $date['class'] = 'current-month';
            }
            if ($date['y'] == $today['y'] && 
                $date['m'] == $today['m'] &&
                $date['d'] == $today['d'])
            {
                $date['class'] = 'current-day';
            }
            $dates["{$date['y']}_{$date['m']}_{$date['d']}"] = $date;
        }
        return $dates; 
    }

    protected function DayFromDate(&$date, $i = 0)
    {
        list($y, $m, $d, $w) = explode(",", $date->format("Y,m,d,w"));
        global $log; $log->logDebug("$i DayFromDate: $m/$d/$y");
        return array("y" => $y, "m" => $m, "d" => $d, "w" => $w, "events" => NULL);
    }


}

