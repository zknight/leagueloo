<? namespace app\admin;
class UserController extends \simp\Controller
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

        $this->AddPreaction("all", "CheckAccess");

    }

    protected function CheckAccess()
    {
        if (!$this->GetUser()->super)
        {
            AddFlash("You don't have sufficient privilege for this action.");
            \Redirect(GetReturnURL());
        }
    }
    
    function Index()
    {
        $offset = $this->GetParam('u');
        $sort_field = $this->GetParam('s');
        $dir = $this->GetParam('d');
        $this->sort_dir = array(
            'login' => 'desc',
            'last_name' => 'desc', 
            'first_name' => 'desc', 
            'created_on' => 'desc', 
            'last_login' => 'desc'
        );
        $dir_swap = array('asc' => 'desc', 'desc' => 'asc');

        $offset = $offset == NULL ? 0 : $offset;
        $sort_field = $sort_field == NULL ? 'login' : $sort_field;
        $dir = $dir == NULL ? 'asc' : $dir;
        $this->dir = $dir;
        $this->sort_dir[$sort_field] = $dir_swap[$dir];
        $this->sort_field = $sort_field;
        $this->offset = $offset;

        $this->per_page = 50;
        $this->users = \simp\Model::Find('User', "1 order by $sort_field collate nocase $dir limit {$this->per_page} offset {$offset};", array());
        $this->pages = array();
        $uc = \R::count('user');
        for ($i=0; $i<$uc; $i+=$this->per_page)
        {
            $this->pages[] = "u=$i";
        }
        $this->cur_page = $offset/$this->per_page;
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
            'main' => array(),
            'program' => array(),
            'team' => array(),
            'plug_in' => array(),
            );
        $this->entities['main']['Club'] = '0';
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
        foreach ($teams as $team)
        {
            $this->entities['team']["{$team->program_name} {$team->division} {$team->name} {$team->gender_str}"] = $team->id;
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
        if (!$user->verified)
        {
            $site_name = GetCfgVar('site_name');
            $subject = "[{$site_name}] New Website Account Confirmation";
            \SendSiteEmail($user, $subject, "confirmation"); 
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
        if (!$user->verified)
        {
            $site_name = GetCfgVar('site_name');
            $subject = "[{$site_name}] New Website Account Confirmation";
            \SendSiteEmail($user, $subject, "confirmation"); 
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
