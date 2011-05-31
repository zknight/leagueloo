<?
namespace app;
class ProgramController extends \simp\Controller
{

    function Setup()
    {
    }
    
    // Actions
    function Index()
    {
        //echo "<strong>program Index with {$this->_params[0]}.</strong>\n";
        $this->StoreLocation();
        global $log;
        if ($this->CheckParam('id'))
        {
            $id = $this->GetParam('id');
            $this->program = \simp\Model::FindById('Program', $id);
        }
        else if ($this->CheckPAram('program'))
        {
            $name = $this->GetParam('program');
            $name = ucfirst($name);
            //$log->logDebug("trying to find model by name: 
            $this->program = \simp\Model::
                FindOne('Program', 'name = ?', array($name));
        }

        if ($this->program->id > 0)
        {
            // load news for this program
            $this->news = \News::FindPublished(
                'Program',
                $this->program->id
            );
            $this->event_data = array('entity_type' => "Program", 'entity_id' => $this->program->id);
            return true;
        }
        else
        {
            $this->NotFound();
            return false;
        }

    }
}
