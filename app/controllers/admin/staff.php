<?php
namespace app\admin;
class StaffController extends \simp\Controller
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
            )

        );

        $this->AddPreaction("all", "CheckAccess");
        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);
        $this->MapAction("configure", "UpdateCfg", \simp\Request::PUT);

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
        $this->configured = \Staff::IsConfigured();
        $this->staff_members = \simp\Model::Find(
            'Staff', 
            '1 order by weight desc',
            array()
        );

        return true;
    }

    public function Configure()
    {
        $this->position_types = \Staff::PositionTypes();
        $this->positions = \Staff::Positions();
        //print_r($this->position_types);
        return true;
    }

    public function UpdateCfg()
    {
        //print_r($this->_form_vars);
        $vars = $this->GetFormVariable('staff');
        $submit = $this->GetFormVariable('submit');
        //print_r($submit);
        if ($submit == "Add Position Type")
        {
            $name = $vars['position_type'];
            $weight = $vars['position_type_weight'];
            \Staff::AddPositionType($weight, $name);
            \Staff::SavePositionTypes();
            $this->SetAction("configure");
        }
        else if ($submit == "Update Position Types")
        {
            foreach ($vars['types'] as $idx => $name)
            {
                \Staff::UpdatePositionType($idx, $vars['weights'][$idx], $name);
            }
            \Staff::SavePositionTypes();
            $this->SetAction("configure");
        }
        else if($submit == "Add Position")
        {
            //print_r($vars);
            for ($idx = 0; $idx < count(\Staff::PositionTypes()); $idx++)
            {
                $name = $vars["position-$idx"];
                $weight = $vars["pos_weight-$idx"];
                if ($name != "" && $weight != "") 
                {
                    \Staff::AddPosition($idx, $weight, $name);
                    \Staff::DoneConfiguring(true);
                }

            }
            \Staff::SavePositions();
            $this->SetAction("configure");
        }
        else if ($submit == "Update Positions")
        {
            print_r($vars);
            for ($idx = 0; $idx < count(\Staff::PositionTypes()); $idx++)
            {
                if (isset($vars["positions-$idx"])) 
                {
                    foreach ($vars["positions-$idx"] as $i => $name)
                    {
                        \Staff::UpdatePosition($idx, $i, $vars["pos_weights-$idx"][$i], $name);
                    }
                }
            }
            \Staff::SavePositions();
            $this->SetAction("configure");
        }
        $this->position_types = \Staff::PositionTypes();
        $this->positions = \Staff::Positions();
        return true;
    }

    public function Add()
    {
        $this->staff = \simp\Model::Create("Staff");
        //$this->team_opts = $this->GetTeamsAsOptions();
        return true;
    }

    public function Create()
    {
        $this->staff = \simp\Model::Create("Staff");
        $vars = $this->GetFormVariable("Staff");
        $vars['file_info'] = $_FILES['image'];
        $this->staff->UpdateFromArray($vars);
        if ($this->staff->Save())
        {
            AddFlash("Staff Member {$this->staff->first_name} {$this->staff->last_name} Added.");
            \Redirect(GetReturnURL());

        }
        else
        {
            $this->SetAction("add");
        }
        return true;
    }

    public function Edit()
    {
        $this->staff = \simp\Model::FindById("Staff", $this->GetParam("id"));
        return true;
    }

    public function Update()
    {
        $this->staff = \simp\Model::FindById("Staff", $this->GetParam("id"));
        $vars = $this->GetFormVariable("Staff");
        $vars['file_info'] = $_FILES['image'];
        $this->staff->UpdateFromArray($vars);
        if ($this->staff->Save())
        {
            AddFlash("Staff Memeber {$this->staff->first_name} {$this->staff->last_name} updated.");
            \Redirect(GetReturnURL());
        }
        else
        {
            //print_r($this->coach);
            $this->SetAction("edit");
        }

        return true;
    }

    public function Remove()
    {
    }
}

