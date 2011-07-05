<?
namespace app;
class ContentController extends \simp\Controller
{
    function Setup()
    {
        $this->RequireAuthorization('index');
        $this->SetLayout('content');
    }

    function Index()
    {
        return true;
    }
}
