<?
namespace app;
class UserController extends \simp\Controller
{
    function Setup()
    {
        $this->AddAction('login', \simp\Request::GET, 'Login');
        $this->AddAction('login', \simp\Request::POST, 'Authorize');
        $this->AddAction('signup', \simp\Request::GET, 'Signup');
        $this->AddAction('signup', \simp\Request::POST, 'Create');
        $this->AddAction('edit', \simp\Request::GET, 'Edit');
        $this->AddAction('edit', \simp\Request::PUT, 'Update');
        $this->AddAction('logout', \simp\Request::GET, 'Logout');
    }

    function Login()
    {
        //$this->user = \simp\Model::FindById($this->GetParam(0));
        $this->user = \simp\Model::Create("User");
        return true;
    }

    function Authorize()
    {
        
        $user_vars = $this->GetFormVariable('User');
        global $log; $log->logDebug("_POST: \n" . print_r($_POST, true));
        $user = \simp\Model::FindOne('User', 'login=?', array($user_vars['login']));
        if ($user && $user->Authenticate($user_vars['password']))
        {
            AddFlash("You are now logged in, {$user->first_name}.");
            SetAuthorizedUser($user->id);
            \Redirect(\Path::main());
        }
        else
        {
            if (!$user) 
            {
                $this->user = \simp\Model::Create('User');
                $this->user->SetError('login', "User {$user_vars['login']} not found.");
            }
            else
            {
                $this->user = $user;
            }
            $this->Render('Login');
            return false;
        }

    }

    function Signup()
    {
        $this->user = \simp\Model::Create("User");
        return true;
    }

    function Create()
    {
    }

    function Edit()
    {
        $this->user = $this->GetUser();
        return true;
    }

    function Update()
    {
    }

    function Logout()
    {
        \ClearSession();
        \AddFlash("You are now logged out.");
        \Redirect(\Path::main());
    }
}
