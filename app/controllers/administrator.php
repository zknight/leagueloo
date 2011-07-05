<?
namespace app;
class AdministratorController extends \simp\Controller
{
    function Setup()
    {
        $this->RequireAuthorization('index');
        $this->RequireAuthorization('clear_cache');
        $this->SetLayout("admin");
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
