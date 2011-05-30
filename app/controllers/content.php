<?
namespace app;
class ContentController extends \simp\Controller
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
