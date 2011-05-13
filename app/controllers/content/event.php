<?
namespace app\content;

class EventController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout('content');

        $this->MapAction("index", "Calendar", \simp\Request::GET);
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
        return true;
    }

    public function Calendar()
    {
        $this->StoreLocation();
        $this->user = CurrentUser();
        if (CheckParam("month") == false)
        {
            $dt = DateTime("now");
            // get this month
            $month = FormatDateTime($dt->getTimeStamp(), "m_Y");
        }
        else
        {
            $month = GetParam("month");
            // should be format: M_YYYY
        }
        list($month, $year) = explode("_", $month);
        // TODO: look up dates between first and last day of given month, inclusive

    }

    public function Show()
    {
        $this->StoreLocation();
        return true;
    }

    public function Add()
    {
        $this->user = CurrentUser();
        $this->event = \simp\Model::Create('Event');
        $this->programs = $this->GetPrograms();
        return true;
    }

    public function Create()
    {

        $this->event = \simp\Model::Create('Event');
        $this->event->UpdateFromArray($this->GetFormVariable("Event"));
        $rerender = false;
        if ($all_day = $this->GetFormVariable("all_day"))
        {
            //$this->event->all_day = $all_day === " " ? true : false;
            $this->event->all_day = !$this->event->all_day;
            $rerender = true;
        }
        else if ($repeats = $this->GetFormVariable("repeat_daily"))
        {
            $this->event->repeat_daily = !$this->event->repeat_daily;
            $rerender = true;
        }
        else if ($repeats = $this->GetFormVariable("repeat_weekly"))
        {
            $this->event->repeat_weekly = !$this->event->repeat_weekly;
            $rerender = true;
        }
        else if ($repeats = $this->GetFormVariable("repeat_monthly"))
        {
            $this->event->repeat_monthly = !$this->event->repeat_monthly;
            $rerender = true;
        }
        else if ($repeats = $this->GetFormVariable("repeat_annually"))
        {
            $this->event->repeat_annually = !$this->event->repeat_annually;
            $rerender = true;
        }
        if ($rerender == true)
        {
            $this->user = CurrentUser();
            $this->programs = $this->GetPrograms();
            $this->Render("add");
            return false;
        }


        echo "<pre>" . print_r($this->_form_vars, true) . "</pre>";
        return false;
    }

    public function Update()
    {
        return false;
    }

    public function Remove()
    {
        return false;
    }

    protected function GetPrograms()
    {
        if ($this->CheckParam('entity') && $this->CheckParam('entity_id'))
        {
            $entity = $this->GetParam('entity');
            $id = $this->GetParam('entity_id');
            if ($this->user->CanEdit($entity, $id))
            {
                return array($id => \simp\Model::FindById($entity, $id)->name);
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
            return $this->user->ProgramsWithPrivilege(\Ability::EDIT);
        }
    }

}

