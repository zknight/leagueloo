<?
namespace app\admin;
class ProgramController extends \simp\RESTController
{
    function Setup()
    {
        $this->Model('Program');
    }

    function Index()
    {
        $this->programs = \simp\DB::Instance()->FindAll('Program');
        return true;
    }

    function Show()
    {
        return true;
    }

    function Add()
    {
        return true;
    }
}
