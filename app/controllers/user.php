<?
namespace app;
class UserController extends \app\AppController
{
    function Setup()
    {
        parent::Setup();
        $this->MapAction('login', 'Authorize', \simp\Request::POST);
        $this->MapAction('signup', 'Create', \simp\Request::POST);
        $this->MapAction('confirm', 'ConfirmPost', \simp\Request::POST);
        $this->MapAction('request_confirmation', 'RequestConfirm', \simp\Request::POST);
        $this->MapAction('edit', 'Update', \simp\Request::PUT);
        $this->MapAction('change_password', 'UpdatePassword', \simp\Request::PUT);
        $this->MapAction('request_association', 'ProcessAssociation', \simp\Request::POST);
        /*
        $this->AddAction('login', \simp\Request::GET, 'Login');
        $this->AddAction('login', \simp\Request::POST, 'Authorize');
        $this->AddAction('signup', \simp\Request::GET, 'Signup');
        $this->AddAction('signup', \simp\Request::POST, 'Create');
        $this->AddAction('confirm', \simp\Request::GET, 'Confirm');
        $this->AddAction('confirm', \simp\Request::POST, 'ConfirmPost');
        $this->AddAction('request_confirmation', \simp\Request::POST, 'RequestConfirm');
        //$this->AddAction('request_password', \simp\Request::POST, 'RequestPassword');
        $this->AddAction('edit', \simp\Request::GET, 'Edit');
        $this->AddAction('edit', \simp\Request::PUT, 'Update');
        $this->AddAction('logout', \simp\Request::GET, 'Logout');
        */
    }

    function Login()
    {
        //$this->user = \simp\Model::FindById($this->GetParam(0));
        if (IsLoggedIn())
        {
            AddFlash("You are already logged in.");
            Redirect(GetReturnURL());
        }
        $this->user = \simp\Model::Create("User");
        return true;
    }

    function Authorize()
    {
        
        $user_vars = $this->GetFormVariable('User');
        global $log; $log->logDebug("_POST: \n" . print_r($_POST, true));
        $user = \simp\Model::FindOne('User', 'login=?', array($user_vars['login']));
        if (!$user)
        {
            $this->user = \simp\Model::Create('User');
            $this->user->SetError('login', "User {$user_vars['login']} not found.");
            $this->Render('Login');
        }
        else if (!$user->verified)
        {
            //\Redirect(\Path::User_RequestConfirmation($user->id));
            $this->user = $user;
            $this->Render('Unconfirmed');
            return false;
        }
        else if ($user->Authenticate($user_vars['password']))
        {
            AddFlash("You are now logged in, {$user->first_name}.");
            $dt = new \DateTime("now");
            $user->last_login = $dt->getTimestamp();
            $user->Save();
            SetAuthorizedUser($user->id);
            \Redirect(GetReturnURL());
        }
        else
        {
            if ($user_vars['request_password'])
            {
                $this->RequestPassword($user);
            }
            $this->user = $user;
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

        $vars = $this->GetFormVariable('User');
        $user = \simp\Model::Create("User");
        $user->UpdateFromArray($vars);
        $created_on = new \DateTime("now");
        $user->created_on = $created_on->format(\DateTimeDefaultFormat());
        $user->verified = false;
        if (!$user->Save())
        {
            $this->user = $user;
            $this->Render('Signup');
            return false;
        }

        global $_SERVER;
        $host = GetCfgVar('site_address');
        if ($host == "") $host = $_SERVER['SERVER_NAME'];
        $site_name = GetCfgVar("site_name");
        $subject = "A message from {$site_name}: website account confirmation";
        $this->SendSiteEmail($user, $subject, "confirmation");
        AddFlash("A confirmation email has been sent to you at {$user->email}.");
        \Redirect(GetReturnURL());
    }

    function ConfirmPost()
    {
        $vars = $this->GetFormVariable('User');
        $this->SetParam('id', $vars['id']);
        $this->SetParam('token', $vars['confirm_check']);
        return $this->Confirm();
    }

    function Confirm()
    {
        $id = $this->GetParam('id');
        $token = $this->GetParam('token'); 
        $user = \simp\Model::FindById('User', $id);
        if ($user->Verify($token))
        {
            $user->Save();
            AddFlash("You have been confirmed.  You can log in now, {$user->login}.");
            Redirect(\Path::user_login());
        }
        else
        {
            AddFlash("The confirmation token you used doesn't match ours.  If you typed in the token, you may have accidentally the token.  If you followed a link in an email that we sent you, then crazy stuff might be going on behind the scenes.  Try requesting another confirmation email, or type or copy/paste the token manually below.");
            $this->Render('Unconfirmed');
            return false;
        }
    }

    function RequestConfirm()
    {
        global $_SERVER;
        $id = $this->GetParam('id');
        $user = \simp\Model::FindById('User', $id);
        $host = GetCfgVar('site_address');
        if ($host == "") $host = $_SERVER['SERVER_NAME'];
        $site_name = GetCfgVar("site_name");
        $subject = "A message from {$site_name}: website account confirmation";
        $this->SendSiteEmail($user, $subject, "confirmation");
        AddFlash("Your request has been sent.  You should be receiving a confirmation "
            . "email very soon.  If you do not, there is likely something wrong with "
            . "the email address you entered when you signed up.  Please contact the "
            . "site administrator if you think this has happened.");
        Redirect(GetReturnURL());
    }

    function RequestAssociation()
    {
        $this->affiliation = \simp\Model::Create('Affiliation');
        $teams = \simp\Model::FindAll('Team');
        $this->teams = array();
        foreach ($teams as $team)
        {
            $this->teams[$team->id] = "{$team->division} {$team->name} {$team->gender_str}";
        }

        $this->user = $this->GetUser();
        return true;
    }

    function ProcessAssociation()
    {
        $this->affiliation = \simp\Model::Create('Affiliation');
        $vars = $this->GetFormVariable('Affiliation');
        $this->affiliation->UpdateFromArray($vars);

        if ($this->affiliation->Save())
        {
            AddFlash("Your request has been sent.");
            \Redirect(\GetReturnURL());
        }
        $this->SetAction("request_association");
        $this->teams = array();
        foreach ($teams as $team)
        {
            $this->teams[$team->id] = "{$team->division} {$team->name} {$team->gender_str}";
        }
        $this->user = $this->GetUser();
        return true;
        
    }

    function Edit()
    {
        $this->user = $this->GetUser();
        return true;
    }

    function Update()
    {
        $id = $this->GetParam('id');
        $vars = $this->GetFormVariable('User');
        $user = \simp\Model::FindById('User', $id);
        $user->UpdateFromArray($vars);
        if (!$user->Save())
        {
            $this->Render('edit');
            return false;
        }
        AddFlash("Account updated.");
        Redirect(GetReturnURL());
    }

    function ChangePassword()
    {
        $this->user = $this->GetUser();
        return true;
    }

    function UpdatePassword()
    {
        $this->user = $this->GetUser();
        $this->user->UpdateFromArray($this->GetFormVariable('User'));
        if (!$user->Save())
        {
            $this->Render('change_password');
            return false;
        }
        AddFlash("New Password Set.");
        Redirect(GetReturnURL());
    }

    function Logout()
    {
        \ClearSession();
        \AddFlash("You are now logged out.");
        \Redirect(\Path::main());
    }

    protected function RequestPassword(&$user)
    {
        $site_name = GetCfgVar('site_name');
        $subject = "A message from {$site_name}: password reset";
        $user->password = RandStr(10);
        $user->password_verification = $user->password;
        $user->Save();
        $this->SendSiteEmail($user, $subject, "new_password");
        global $log;
        $log->logDebug("random password: {$user->password}");
        AddFlash("Your request has been sent.");
        \Redirect(\Path::user_login());
    }

    protected function SendSiteEmail($user, $subject, $message)
    {
        global $_SERVER;
        $from = GetCfgVar('site_email');
        $to = "{$user->first_name} {$user->last_name} <{$user->email}>";
        $host = GetCfgVar('site_address');
        if ($host == "") $host = $_SERVER['SERVER_NAME'];
        $email_data = array(
            'to' => $to, 
            'from' => $from,
            'subject' => $subject,
            'type' => $user->email_html ? "html" : "plain",
            'data' => array(
                'site_name' => GetCfgVar('site_name'),
                'host' => $host,
                'user' => $user,
            ),
        );

        return \simp\Email::Send($message, $email_data);
    }

}
