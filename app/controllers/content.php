<?
namespace app;
class ContentController extends \app\AppController
{
    function Setup()
    {
        $this->RequireAuthorization('index');
        $this->SetLayout('content');
    }

    protected function CheckAccess()
    {
        if (!$this->GetUser()->super)
        {
            AddFlash("You don't have sufficient privilege for this action.");
            \Redirect(GetReturnURL());
        }
    }

    public function Index()
    {
        return true;
    }

}
