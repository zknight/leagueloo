<?php
namespace app\content;

class CampController extends \simp\Controller
{

    function Setup()
    {
        $this->SetLayout('content');

        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);
        $this->MapAction("configure", "UpdateConfig", \simp\Request::PUT);

        $this->RequireAuthorization(
            array(
                'index',
                'show',
                'add',
                'edit',
                'delete',
                'configure'
            )
        );

    }

    public function Index()
    {
        $this->StoreLocation();
        $dt = new \DateTime("now");

        $this->camps = array();
        $this->camps['upcoming'] = \Camp::GetUpcoming();
        $this->camps['past'] = \Camp::GetPast();

        return true;
    }

    public function Add()
    {
        $this->camp = \simp\Model::Create("Camp");
        $this->leagues = \simp\Model::Find(
            "Program", 
            "type = ?",
            array(\Program::LEAGUE)
        );
        return true;
    }

    public function Create()
    {
        $this->camp = \simp\Model::Create("Camp");
        $vars = $this->GetFormVariable('Camp');
        $this->camp->UpdateFromArray($vars);
        $this->camp->file_info = $_FILES;
        if ($this->camp->Save())
        {
            AddRecentUpdate(
                'camp',
                $this->camp->id,
                $this->camp->name,
                "camps", 
                "camp", 
                "show", 
                $this->camp->short_name);
            AddFlash("Camp {$this->camp->name} Created.");
            \Redirect(GetReturnURL());
        }
        else
        {
            $this->leagues = \simp\Model::Find(
                "Program", 
                "type = ?",
                array(\Program::LEAGUE)
            );
            $this->SetAction("add");
        }
        return true;
    }

    public function Edit()
    {
        $this->camp = \simp\Model::FindById("Camp", $this->GetParam('id'));
        $this->leagues = \simp\Model::Find(
            "Program", 
            "type = ?",
            array(\Program::LEAGUE)
        );
        return true;
    }

    public function Update()
    {
        $this->camp = \simp\Model::FindById("Camp", $this->GetParam('id'));
        $vars = $this->GetFormVariable('Camp');
        $this->camp->UpdateFromArray($vars);
        $this->camp->file_info = $_FILES;
        if ($this->camp->Save())
        {
            AddRecentUpdate(
                'camp',
                $this->camp->id,
                $this->camp->name,
                "camps", 
                "camp", 
                "show", 
                $this->camp->short_name);
            AddFlash("Camp {$this->camp->name} Updated.");
            \Redirect(GetReturnURL());
        }
        else
        {
            
            $this->leagues = \simp\Model::Find(
                "Program", 
                "type = ?",
                array(\Program::LEAGUE)
            );
            $this->SetAction("Edit");
        }
        return true;
    }

    public function Remove()
    {
        return false;
    }

    public function Configure()
    {
        //$this->StoreLocation();
        $this->show_past_camps = $this->LoadVariable('camps_show_past_camps', '0');
        return true;
    }

    public function UpdateConfig()
    {
        $vars = $this->GetFormVariable('CfgVar');
        $cfg_var = \simp\Model::FindById('CfgVar', $this->GetParam('id'));
        $cfg_var->UpdateFromArray($vars);
        $cfg_var->Save();
        \Redirect(\Path::content_camp_configure());
    }
}
