<?
namespace app;
class ProgramController extends \simp\Controller
{

    function Setup()
    {
        $this->AddAction('name', \simp\Request::GET, 'Index');
        $this->AddAction('id', \simp\Request::GET, 'Index');
    }
    
    // Actions
    function Index()
    {
        echo "<strong>program Index with {$this->_params[0]}.</strong>\n";
        return false;
    }
}
