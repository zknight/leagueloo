<?
namespace app\admin;
class ScheduleController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            array(
                'index',
                'add',
                'show',
                'edit',
                'fields',
                'upload',
                'load'
            )
        );

        $this->MapAction("upload", "Load", \simp\Request::POST);
        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
    } 

    function Index()
    {
        $this->StoreLocation();
        //$this->matches = \simp\Model::FindAll("Match");
        $now = new \DateTime("now");
        $this->schedules = \simp\Model::Find("Schedule", "end_date >= ?", array($now->getTimestamp()));
        $this->past_schedules = \simp\Model::Find("Schedule", "end_date < ?", array($now->getTimestamp()));
        //$this->matches = \Schedule::GetScheduleByDate();
        return true;
    }

    function Add()
    {
        $this->schedule = \simp\Model::Create("Schedule");
        return true;
    }

    function Edit()
    {
        $this->schedule = \simp\Model::FindById("Schedule", $this->GetParam('id'));
        return true;
    }

    function Create()
    {
        $this->schedule = \simp\Model::Create("Schedule");
        $vars = $this->GetFormVariable("Schedule");
        $this->schedule->UpdateFromArray($vars);
        if ($this->schedule->Save())
        {
            \AddFlash("Schedule added.  Now create matches or upload");
            \Redirect(\GetReturnURL());
        }
        else
        {
            $this->SetAction("add");
        }

        return true;
    }

    function Update()
    {
        $this->schedule = \simp\Model::FindById("Schedule", $this->GetParam('id'));
        $vars = $this->GetFormVariable("Schedule");
        $this->schedule->UpdateFromArray($vars);
        if ($this->schedule->Save())
        {
            \AddFlash("Schedule updated.");
            \Redirect(\GetReturnURL());
        }
        else
        {
            $this->SetAction("edit");
        }
        return true;
    }

    function Show()
    {
        $this->StoreLocation();
        $this->schedule = \simp\Model::FindById("Schedule", $this->GetParam('id'));
        return true;
    }

    function Fields()
    {
        $this->StoreLocation();
        $this->schedule = new \Schedule();
        return true;
    }

    function Upload()
    {
        $this->schedule = \simp\Model::FindById("Schedule", $this->GetParam('id'));
        //$this->schedule = new \Schedule();//\simp\Model::Create("Schedule");
        return true;
    }

    function Load()
    {
        //$this->schedule = new \Schedule();//;\simp\Model::Create("Schedule");
        $this->schedule = \simp\Model::FindById("Schedule", $this->GetParam('id'));
        $vars = $this->GetFormVariable('Schedule');
        //$this->schedule->level = $vars['level'];
        //$this->schedule->ImportFile($_FILES['schedule']);
        if ($_FILES['schedule']['type'] != 'text/csv')
        {
            AddFlash("File must be in CSV format.");
            $this->SetAction('upload');
        }
        else if ($this->schedule->ImportFile($_FILES['schedule']['tmp_name']))
        {
            AddFlash("{$this->schedule->new_count} Matches Added.  {$this->schedule->update_count} Matches Updated.");
            Redirect(GetReturnURL());
        }
        else
        {
            AddFlash("Failed to import.");
            $this->SetAction('upload');
        }
        return true;
    }
}
