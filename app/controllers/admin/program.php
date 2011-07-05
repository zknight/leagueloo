<?
namespace app\admin;
class ProgramController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            array(
                'index',
                'show',
                'add',
                'edit',
                'delete',
                'privileges'
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
        $this->program = \simp\Model::FindById('Program', $this->GetParam('id'));
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
        $this->program = \simp\Model::Create('Program');
        $vars = $this->GetFormVariable('Program');
        $vars['file_info'] = $_FILES['image'];
        $this->program->UpdateFromArray($vars);
        if ($this->program->Save())
        {
            AddFlash("Program {$this->program->name} created.");
            \Redirect(\Path::admin_program());
        }
        else
        {
            $this->SetAction("add");
        }
        return true;
    }

    function Update()
    {
        $vars = $this->GetFormVariable('Program');
        $this->program = \simp\Model::FindById('Program', $this->GetParam('id'));
        $vars['file_info'] = $_FILES['image'];
        $this->program->UpdateFromArray($vars);
        if ($this->program->Save())
        {
            AddFlash("Program {$this->program->name} updated.");
            \Redirect(\Path::admin_program());
        }
        else
        {
            $this->SetAction("edit");
        }
        return true;
    }

    function Remove()
    {
        $program = \simp\Model::FindById('Program', $this->GetParam('id'));
        $name = $program->name;
        if ($program->id > 0)
        {
            $program->Delete();
            AddFlash("Program $name deleted.");
        }
        \Redirect(\Path::admin_program());
    }

    function Privileges()
    {
        echo "TODO: implement this.";
        return false;
    }
}
