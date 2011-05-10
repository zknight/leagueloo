<?
namespace app\content;

class EventController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout('content');
    }

    public function Index()
    {
        // load all articles that this user has access to
        $this->StoreLocation();
        $this->user = CurrentUser();
        $this->upcoming_events = $this->user->GetUpcomingEvents();
        $this->expired_events = $this->user->GetExpiredEvents();
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
        $this->event = \simp\Model::Create('Event');
        $this->programs = $this->GetPrograms();
        return true;
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

