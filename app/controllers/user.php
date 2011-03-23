<?
namespace app;
class UserController extends \simp\Controller
{
    function Setup()
    {
        $this->AddAction('login', \simp\Request::GET, 'Login');
        $this->AddAction('authorize', \simp\Request::POST, 'Authorize');
        $this->AddAction('signup', \simp\Request::GET, 'Signup');
        $this->AddAction('create', \simp\Request::POST, 'Create');
        $this->AddAction('edit', \simp\Request::GET, 'Edit');
        $this->AddAction('update', \simp\Request::PUT, 'Update');
    }

    function Login()
    {
    }

    function Authorize()
    {
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
