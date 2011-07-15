<?php
namespace app\admin;
class CoachesController extends \simp\Controller
{
    public function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            array(
                'index',
                'show',
                'add',
                'edit',
                'delete',
                'activate',
                'deactivate'
            )

        );

        $this->AddPreaction("all", "CheckAccess");
        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);

    }

    protected function CheckAccess()
    {
        if (!$this->GetUser()->super)
        {
            AddFlash("You don't have sufficient privilege for this action.");
            \Redirect(GetReturnURL());
        }
    }

    public function Index()
    {
        $this->StoreLocation();
        $this->coaches = \simp\Model::Find(
            'Coach', 
            '1 order by active desc',
            array()
        );

        return true;
    }

    public function Add()
    {
        $this->coach = \simp\Model::Create("Coach");
        //$this->team_opts = $this->GetTeamsAsOptions();
        $this->teams = $this->GetTeams();
        return true;
    }

    public function Create()
    {
        $this->coach = \simp\Model::Create("Coach");
        $vars = $this->GetFormVariable("Coach");
        $vars['file_info'] = $_FILES;
        $this->coach->UpdateFromArray($vars);
        if ($this->coach->Save())
        {
            AddFlash("Coach {$this->coach->first_name} {$this->coach->last_name} Added.");
            \Redirect(GetReturnURL());

        }
        else
        {
            $this->teams = $this->GetTeams();
            $this->SetAction("add");
        }
        return true;
    }

    public function Edit()
    {
        $this->coach = \simp\Model::FindById("Coach", $this->GetParam("id"));
        //print_r($this->coach);
        $this->teams = $this->GetTeams();
        return true;
    }

    public function Update()
    {
        $this->coach = \simp\Model::FindById("Coach", $this->GetParam("id"));
        $vars = $this->GetFormVariable("Coach");
        $vars['file_info'] = $_FILES;
        $this->coach->UpdateFromArray($vars);
        if ($this->coach->Save())
        {
            AddFlash("Coach {$this->coach->first_name} {$this->coach->last_name} updated.");
            \Redirect(GetReturnURL());
        }
        else
        {
            $this->teams = $this->GetTeams();
            //print_r($this->coach);
            $this->SetAction("edit");
        }

        return true;
    }

    public function Deactivate()
    {
        $coach = \simp\Model::FindById("Coach", $this->GetParam('id'));
        if ($coach-> id == 0)
        {
            AddFlash("Unable to find that coach.  Contact system administrator.");
        }
        else
        {
            $coach->active = false;
            $coach->Save();
            AddFlash("Coach {$this->first_name} {$this->last_name} deactivated.");
        }
        \Redirect(GetReturnURL());
    }

    public function Activate()
    {
        $coach = \simp\Model::FindById("Coach", $this->GetParam('id'));
        if ($coach->id == 0)
        {
            AddFlash("Unable to find that coach.  Contact system administrator.");
        }
        else
        {
            $coach->active = true;
            $coach->Save();
            AddFlash("Coach {$this->first_name} {$this->last_name} activated.");
        }
        \Redirect(GetReturnURL());
    }

    public function Remove()
    {
    }

    protected function GetTeams()
    {
        return \simp\Model::Find(
            'Team', 
            '1 order by program_id, year, gender',
            array()
        );
    }

}
