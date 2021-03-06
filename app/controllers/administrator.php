<?
namespace app;
class AdministratorController extends \simp\Controller
{
    function Setup()
    {
        $this->RequireAuthorization('index');
        $this->RequireAuthorization('clear_cache');
        $this->SetLayout("admin");
        $this->AddPreaction("all", "CheckAccess");
    }

    protected function CheckAccess()
    {
        if (!$this->GetUser()->super)
        {
            AddFlash("You don't have sufficient privilege for this action.");
            \Redirect(GetReturnURL());
        }
    }

    function Index()
    {
        $this->StoreLocation();
        return true;
    }

    function ClearCache()
    {
        \Cache::Reset();
        AddFlash("Cache cleared.");
        \Redirect(GetReturnURL());
    }

}
