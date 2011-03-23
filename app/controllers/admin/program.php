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
        $this->programs = \simp\Model::FindAll('Program');
        return true;
    }

    function Show()
    {
        return true;
    }

    function Add()
    {
        $this->program = \simp\Model::Create('Program');
        return true;
    }

    function Edit()
    {
        global $log;
        $this->program = \simp\Model::FindById('Program', $this->GetParam(0));
        $log->logDebug("program \n " . print_r($this->program, true));
        $program = $this->program;
        $log->logDebug("program id: " . $program->id);
        if ($this->program->id > 0)
        {
            return true;
        }
        else
        {
           \Redirect(\Path::admin_program());
        }
    }
    function Create()
    {
        // TODO: add validation and check to make sure it is saved, plus flash and redirect!
        $vars = $this->GetFormVariable('Program');
        $program = \simp\Model::Create('Program');
        $program->name = $vars['name'];
        $program->description = $vars['description'];
        $program->Save();
        \Redirect(\Path::admin_program());
        return false;
    }

    function Update()
    {
        $vars = $this->GetFormVariable('Program');
        $program = \simp\Model::FindById('Program', $this->GetParam(0));
        $program->UpdateFromArray($vars);
        $program->Save();
        \Redirect(\Path::admin_program());
    }

    function Remove()
    {
        $program = \simp\Model::FindById('Program', $this->GetParam(0));
        if ($program->id > 0)
        {
            $program->Delete();
        }
        \Redirect(\Path::admin_program());
    }
}
