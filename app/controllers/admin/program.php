<?
namespace app\admin;
class ProgramController extends \simp\Controller
{
    function Setup()
    {
        $this->RequireAuthorization(
            array(
                'index',
                'show',
                'add',
                'edit',
                'delete'
                )
            );

        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);
    }

    function Index()
    {
        $this->programs = \simp\Model::FindAll('Program', 'order by weight asc');
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
        $this->program = \simp\Model::FindById('Program', $this->GetParam('id'));
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
        $program = \simp\Model::FindById('Program', $this->GetParam('id'));
        $program->UpdateFromArray($vars);
        $program->Save();
        \Redirect(\Path::admin_program());
    }

    function Remove()
    {
        $program = \simp\Model::FindById('Program', $this->GetParam('id'));
        if ($program->id > 0)
        {
            $program->Delete();
        }
        \Redirect(\Path::admin_program());
    }
}
