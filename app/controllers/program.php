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
        //echo "<strong>program Index with {$this->_params[0]}.</strong>\n";
        global $log;
        $name_or_id = $this->GetParam(0);
        if (preg_match("/^\d/", $name_or_id))
        {
            $this->program = \simp\DB::Instance()->Load('Program', $name_or_id);
        }
        else 
        {
            $name = ucfirst($name_or_id);
            //$log->logDebug("trying to find model by name: 
            $this->program = \simp\DB::Instance()
                ->FindOne('Program', 'name = ?', array($name));
        }
        return true;
    }
}
