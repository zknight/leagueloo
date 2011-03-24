<? namespace app\admin;
class UserController extends \simp\RESTController
{
    function Setup()
    {
        $this->Model('User');
    }
    
    function Index()
    {
        $this->users = \simp\Model::FindAll('User');
        return true;
    }

    function Show()
    {
        return true;
    }

    function Add()
    {
        $this->user = \simp\Model::Create('User');
        $programs = \simp\Model::FindAll('Program');
        //$team_names = \R::getCol("select name from team");
        //$app_names = \R::getCol("select name from app");
        $this->entities = array('program' => array() /* 'team' => array(), 'app' => array()*/);
        foreach ($programs as $program)
        {
            $this->entities['program'][$program->name] = $program->id;
        }
        $this->user->timezone = GetCfgVar("default_timezone");
        return true;
    }

    function Edit()
    {
        $this->user = \simp\Model::FindById('User', $this->GetParam(0));
        //echo"<pre>" . print_r($this->user, true) . "</pre>";
        $programs = \simp\Model::FindAll('Program');
        //$team_names = \R::getCol("select name from team");
        //$app_names = \R::getCol("select name from app");
        $this->entities = array('program' => array() /* 'team' => array(), 'app' => array()*/);
        foreach ($programs as $program)
        {
            $this->entities['program'][$program->name] = $program->id;
        }
        if ($this->user->id > 0)
        {
            return true;
        }
        else
        {
            \Redirect(\Path::admin_program());
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
        $user->Save();
        \Redirect(\Path::admin_user());
        return false;
    }

    function Update()
    {
        $user = \simp\Model::FindById('User', $this->GetParam(0));
        $user_vars = $this->GetFormVariable('User');
        global $log;
        $log->logDebug("user_vars: \n" . print_r($user_vars, true));
        $user->UpdateFromArray($user_vars);
        $abilities = $this->GetFormVariable('Ability');
        $log->logDebug("abilities: \n" . print_r($abilities, true));
        $user->UpdateAbilities($abilities);
        $user->Save();
        \Redirect(\Path::admin_user());
    }

    function Remove()
    {
        $user = \simp\Model::FindById('User', $this->GetParam(0));
        if ($user->id > 0)
        {
            $user->Delete();
        }
        \Redirect(\Path::admin_user());
    }

}           
