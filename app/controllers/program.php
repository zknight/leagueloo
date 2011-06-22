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
        else if ($this->CheckParam('program'))
        {
            $name = $this->GetParam('program');
            $name = ucfirst($name);
            //$log->logDebug("trying to find model by name: 
            $this->program = \simp\Model::
                FindOne('Program', 'name = ?', array($name));
        }

        if ($this->program->id > 0)
        {
            SetEntity('Program', $this->program->id, $this->program->name);
            // load news for this program
            $this->news = \News::FindPublished(
                'Program',
                $this->program->id
            );

            $this->RenderSpecialProgram();
            return true;
        }
        else
        {
            $this->NotFound();
            return false;
        }

    }

    protected function RenderSpecialProgram()
    {
        switch ($this->program->type)
        {
        case \Program::LEAGUE:
            break;
        case \Program::TOURNAMENT:
            $this->upcoming_tournaments = \Tournament::GetUpcoming();
            $this->past_tournaments = \Tournament::GetPast();
            $this->SetAction("tournament_index");
            break;
        case \Program::CAMP:
            break;
        }
    }

}
