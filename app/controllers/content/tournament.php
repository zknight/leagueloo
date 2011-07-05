<?php
namespace app\content;

class TournamentController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout('content');

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
        $this->StoreLocation();
        $dt = new \DateTime("now");

        $this->tournaments = array();
        $this->tournaments['upcoming'] = \simp\Model::Find(
            "Tournament",
            "start >= ? order by start asc",
            array($dt->getTimestamp())
        );

        $this->tournaments['past'] = \simp\Model::Find(
            "Tournament",
            "start < ? order by start asc",
            array($dt->getTimestamp())
        );

        return true;
    }

    public function Add()
    {
        $this->tournament = \simp\Model::Create("Tournament");
        $this->leagues = \simp\Model::Find(
            "Program", 
            "type = ?",
            array(\Program::LEAGUE)
        );
        return true;
    }

    public function Create()
    {
        $this->tournament = \simp\Model::Create("Tournament");
        $vars = $this->GetFormVariable('Tournament');
        $this->tournament->UpdateFromArray($vars);
        $this->tournament->file_info = $_FILES;
        if ($this->tournament->Save())
        {
            AddRecentUpdate(
                'tournament',
                $this->tournament->id,
                $this->tournament->name,
                "tournaments", 
                "tournament", 
                "show", 
                $this->tournament->short_name);
            AddFlash("Tournament {$this->tournament->name} Created.");
            \Redirect(GetReturnURL());
        }
        else
        {
            
            $this->leagues = \simp\Model::Find(
                "Program", 
                "type = ?",
                array(\Program::LEAGUE)
            );
            $this->Render("Add");
            return false;
        }
    }

    public function Edit()
    {
        $this->tournament = \simp\Model::FindById("Tournament", $this->GetParam('id'));
        $this->leagues = \simp\Model::Find(
            "Program", 
            "type = ?",
            array(\Program::LEAGUE)
        );
        return true;
    }

    public function Update()
    {
        $this->tournament = \simp\Model::FindById("Tournament", $this->GetParam('id'));
        $vars = $this->GetFormVariable('Tournament');
        $this->tournament->UpdateFromArray($vars);
        $this->tournament->file_info = $_FILES;
        if ($this->tournament->Save())
        {
            AddRecentUpdate(
                'tournament',
                $this->tournament->id,
                $this->tournament->name,
                "tournaments", 
                "tournament", 
                "show", 
                $this->tournament->short_name);
            AddFlash("Tournament {$this->tournament->name} Updated.");
            \Redirect(GetReturnURL());
        }
        else
        {
            
            $this->leagues = \simp\Model::Find(
                "Program", 
                "type = ?",
                array(\Program::LEAGUE)
            );
            $this->Render("Edit");
            return false;
        }
    }

    public function Remove()
    {
        return false;
    }
}
