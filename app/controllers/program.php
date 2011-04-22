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
        global $log;
        if ($id = $this->GetParam('id'))
        {
            $this->program = \simp\Model::FindById('Program', $id);
        }
        else if ($name = $this->GetParam('program'))
        {
            $name = ucfirst($name);
            //$log->logDebug("trying to find model by name: 
            $this->program = \simp\Model::
                FindOne('Program', 'name = ?', array($name));
        }

        if ($this->program->id > 0)
        {
            // load news for this program
            $this->news = \simp\Model::Find(
                "News", 
                "entity_type = 'Program' and entity_id = ?",
                array($this->program->id));
            return true;
        }
        else
        {
            $this->NotFound();
            return false;
        }

    }
}
