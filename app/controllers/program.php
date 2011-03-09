<?
namespace app;
class ProgramController extends \simp\Controller
{

    function Setup()
    {
        $this->AddAction('show', \simp\Request::GET, 'Show');
    }
    
    // Actions
    function Index()
    {
        echo "<strong>program Index with {$this->_params}.</strong>\n";
        return false;
    }

    function Show()
    {
        global $log;
        echo "<strong>Showing " . $this->_params[0] . "</strong>\n";
        $log->logDebug("in program: params = {$this->_params[0]}");
    }
}
