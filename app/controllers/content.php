<?
namespace app;
class ContentController extends \app\AppController
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
