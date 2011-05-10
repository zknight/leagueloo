<? namespace app\admin;
class UserController extends \simp\Controller
{
    function Setup()
    {
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
    
    function Index()
    {
        $this->users = \simp\Model::FindAll('User');
        return true;
    }

    function Show()
    {
        $this->user = \simp\Model::FindById('User', $this->GetParam('id'));
        //$this->LoadEntitiesForAbilities();
        return true;
    }

    protected function LoadEntitiesForAbilities()
    {
        $this->entities = array(
            'program' => array(),
            'team' => array(),
            'plug_in' => array()
            );
        $programs = \simp\Model::FindAll('Program');
        foreach ($programs as $program)
        {
            $this->entities['program'][$program->name] = $program->id;
        }
        $plug_ins = \simp\Model::Find('PlugIn', 'enabled = ?', array(true));
        foreach ($plug_ins as $plug_in)
        {
            $this->entities['plug_in'][$plug_in->name] = $plug_in->id;
        }
        $teams = \simp\Model::FindAll('Team');
        foreach ($plug_ins as $plug_in)
        {
            $this->entities['team'][$team->name] = $team->id;
        }
    }

    function Add()
    {
        $this->user = \simp\Model::Create('User');
        //$programs = \simp\Model::FindAll('Program');
        $this->LoadEntitiesForAbilities();
        $this->user->timezone = GetCfgVar("default_timezone");
        return true;
    }

    function Edit()
    {
        $this->user = \simp\Model::FindById('User', $this->GetParam('id'));
        $this->LoadEntitiesForAbilities();
        if ($this->user->id > 0)
        {
            return true;
        }
        else
        {
            AddFlash("Unable to find that user.");
            \Redirect(\Path::admin_user());
        }
    }

    function Create()
    {
        $vars = $this->GetFormVariable('User');
        $user = \simp\Model::Create('User');
        $user->UpdateFromArray($vars);
        $created_on = new \DateTime("now");
        $user->created_on = $created_on->format(\DateTimeDefaultFormat());
        $abilities = $this->GetFormVariable('Ability');
        $user->UpdateAbilities($abilities);
        if (!$user->Save())
        {
            $this->user = $user;
            $this->LoadEntitiesForAbilities();
            $this->Render('Add');
            return false;
        }
        AddFlash("User {$user->login} created.");
        \Redirect(\Path::admin_user());
        return false;
    }

    function Update()
    {
        $user = \simp\Model::FindById('User', $this->GetParam('id'));
        $user_vars = $this->GetFormVariable('User');
        global $log;
        $log->logDebug("user_vars: \n" . print_r($user_vars, true));
        $user->UpdateFromArray($user_vars);
        $abilities = $this->GetFormVariable('Ability');
        $log->logDebug("abilities: \n" . print_r($abilities, true));
        $user->UpdateAbilities($abilities);
        if (!$user->Save())
        {
            $this->user = $user;
            $this->LoadEntitiesForAbilities();
            $this->Render('Edit');
            return false;
        }
        AddFlash("User {$user->login} updated.");
        \Redirect(\Path::admin_user());
    }

    function Remove()
    {
        $user = \simp\Model::FindById('User', $this->GetParam('id'));
        if ($user->id > 0)
        {
            $user->Delete();
        }
        \Redirect(\Path::admin_user());
    }

}           
