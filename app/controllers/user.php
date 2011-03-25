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
    }

    function Login()
    {
        //$this->user = \simp\Model::FindById($this->GetParam(0));
        $this->user = \simp\Model::Create("User");
        return true;
    }

    function Authorize()
    {
        
        \Redirect(\Path::main());
    }

    function Signup()
    {
    }

    function Create()
    {
    }

    function Edit()
    {
    }

    function Update()
    {
    }
}
