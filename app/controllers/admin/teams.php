<?
namespace app\admin;
class TeamsController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            array(
                'index',
                'show',
                'add',
                'edit',
                'delete'
            )
        );

        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);

    }

    public function Index()
    {
        $this->StoreLocation();
        $this->teams_by_program = array();
        // load programs that can have teams associated
        $this->programs = \simp\Model::Find('Program', 'allow_teams = ? order by weight asc', array(true));
        foreach ($this->programs as $program)
        {
            $this->teams_by_program[$program->name] = 
                \simp\Model::Find('Team', 'program_id = ? order by gender, year asc', array($program->id));
        }
        return true;
    }

    public function Show()
    {
        $this->StoreLocation();
        $this->team = \simp\Model::FindById('Team', $this->GetParam('id'));
        return true;
    }

    public function Add()
    {
        $this->team = \simp\Model::Create('Team');
        $this->programs = $this->GetPrograms();
        return true;
    }

    public function Edit()
    {
        $this->team = \simp\Model::FindById('Team', $this->GetParam('id'));
        $this->programs = $this->GetPrograms();
        return true;
    }

    public function Create()
    {
        $this->team = \simp\Model::Create('Team');
        $vars = $this->GetFormVariable('Team');
        $vars['file_info'] = $_FILES['image'];
        global $log; $log->logDebug("admin/teams/create File Info: " . print_r($vars['file_info'], true));
        $this->team->UpdateFromArray($vars);
        if ($this->GetParam('format') == 'json')
        {
            if (!$this->team->Save())
            {
                $errors = array('status' => -1, 'message' => GetErrorsFor($this->team));
                echo json_encode($errors);
                return false;
            }
            else
            {
                $ok = array(
                    'status' => 0,
                    'message' => "Team Page Created for {$this->team->division} {$this->team_gender_str} {$this->team->name}",
                    'team' => array(
                        'name' => $this->team->name,
                        'id' => $this->team->id,
                        'league' => $this->team->program_name,
                        'gender' => $this->team->gender_str)
                );
                echo json_encode($ok);
                return false;
            }

        }
        if (!$this->team->Save())
        {
            $this->programs = $this->GetPrograms();
            $this->Render('Add');
            return false;
        }
        AddFlash("Team page created for {$this->team->division} {$this->team->gender_str} {$this->team->name}");
        \Redirect(GetReturnURL());
    }

    public function Update()
    {
        $this->team = \simp\Model::FindById('Team', $this->GetParam('id'));
        $vars = $this->GetFormVariable('Team');
        $vars['file_info'] = $_FILES['image'];
        $this->team->UpdateFromArray($vars);
        if (!$this->team->Save())
        {
            $this->programs = $this->GetPrograms();
            $this->Render('Edit');
            return false;
        }
        AddFlash("Team page updated for {$this->team->division} {$this->team->gender_str} {$this->team->name}");
        \Redirect(GetReturnURL());

    }

    public function Remove()
    {
        $team = \simp\Model::FindById('Team', $this->GetParam('id'));
        $team_name = "{$team->division} {$team->gender_str} {$team->name}";
        if ($team->id > 0)
        {
            $team->Delete();
            AddFlash("Team page for $team_name removed.");
            // TODO: need to remove events and articles
        }
        else
        {
            AddFlash("A strange problem has occurred trying to delete (ID10-T programmer error).");
        }
        \Redirect(GetReturnURL());
    }

    protected function GetPrograms()
    {
        //return $this->GetUser()->ProgramsWithPrivilege(\Ability::ADMIN, "has_teams = ?", array(true));
        return $this->GetUser()->OptionsForEntitiesWithPrivilege("Program", \Ability::ADMIN, "allow_teams = ?", array(true));
    }

}
