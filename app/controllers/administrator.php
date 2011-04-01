<?
namespace app;
class AdministratorController extends \simp\Controller
{
    function Setup()
    {
        $this->RequireAuthorization('Index');
    }

    function Index()
    {
        return true;
    }
}
