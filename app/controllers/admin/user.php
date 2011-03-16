<? namespace app\admin;
class UserController extends \simp\RESTController
{
    function Setup()
    {
        $this->Model('User');
    }
    
    function Index()
    {
        $this->users = \simp\DB::Instance()->FindAll('User');
        return true;
    }

    function Show()
    {
        return true;
    }

    function Add()
    {
        $this->user = \simp\DB::Instance()->Create('User');
        $this->user->timezone = GetCfgVar("default_timezone");
        return true;
    }

    function Edit()
    {
        $this->user = \simp\DB::Instance()->Load('User', $this->GetParam(0));
        //$this->programs = \simp\DB::Instance()->FindAll('Program');
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
        $user = \simp\DB::Instance()->Create('User');
        $user->UpdateFromArray($vars);
        $created_on = new DateTime("now");

        $user->created_on = $created_on->format(\DateTimeDefaultFormat());
        \simp\DB::Instance()->Save($user);
        \Redirect(\Path::admin_user());
        return false;
    }

    function Update()
    {
        $user = \simp\DB::Instance()->Load('User', $this->GetParam(0));
        if ($user->id > 0)
        {
            \simp\DB::Instance()->Delete($user);
        }
        \Redirect(\Path::admin_program());
    }
}           
