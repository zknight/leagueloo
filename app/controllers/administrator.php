<?
namespace app;
class AdministratorController extends \simp\Controller
{
    function Setup()
    {
        $this->RequireAuthorization('index');
    }

    function Index()
    {
        return true;
    }
}
