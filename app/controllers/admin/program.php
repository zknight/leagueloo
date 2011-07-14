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
        //$this->programs = \simp\Model::FindAll('Program', 'order by weight asc');
        $this->programs = $this->GetUser()->FindEntitiesWithPrivilege("program");
        return true;
    }

    function Show()
    {
        if (!$this->GetUser()->CanAdmin("Program", $this->GetParam('id')))
            \Redirect(GetReturnURL());
        $this->program = \simp\Model::FindById('Program', $this->GetParam('id'));
        return true;
    }

    function Add()
    {
        if (!$this->GetUser()->super)
            \Redirect(GetReturnURL());
        $this->program = \simp\Model::Create('Program');
        return true;
    }

    function Edit()
    {
        global $log;
        if (!$this->GetUser()->CanAdmin("Program", $this->GetParam('id')))
            \Redirect(GetReturnURL());
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
        if (!$this->GetUser()->super)
            \Redirect(GetReturnURL());
        $this->program = \simp\Model::Create('Program');
        $vars = $this->GetFormVariable('Program');
        $vars['file_info'] = $_FILES['image'];
        $this->program->UpdateFromArray($vars);
        if ($this->program->Save())
        {
            \Cache::Delete('programs');
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
        if (!$this->GetUser()->CanAdmin("Program", $this->GetParam('id')))
            \Redirect(GetReturnURL());
        $vars = $this->GetFormVariable('Program');
        $this->program = \simp\Model::FindById('Program', $this->GetParam('id'));
        $vars['file_info'] = $_FILES['image'];
        $this->program->UpdateFromArray($vars);
        if ($this->program->Save())
        {
            \Cache::Delete('programs');
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
        if (!$this->GetUser()->super)
            \Redirect(GetReturnURL());
        $program = \simp\Model::FindById('Program', $this->GetParam('id'));
        $name = $program->name;
        if ($program->id > 0)
        {
            $program->Delete();
            \Cache::Delete('programs');
            AddFlash("Program $name deleted.");
        }
        \Redirect(\Path::admin_program());
    }

    function Privileges()
    {
        
    }
}
