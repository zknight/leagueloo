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
        $this->programs = \simp\DB::Instance()->FindAll('Program');
        return true;
    }

    function Show()
    {
        return true;
    }

    function Add()
    {
        $this->program = \simp\DB::Instance()->Create('Program');
        return true;
    }

    function Edit()
    {
        $this->program = \simp\DB::Instance()->Load('Program', $this->GetParam(0));
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
        $program = \simp\DB::Instance()->Create('Program');
        $program->name = $vars['name'];
        $program->description = $vars['description'];
        \simp\DB::Instance()->Save($program);
        \Redirect(\Path::admin_program());
        return false;
    }

    function Update()
    {
        $vars = $this->GetFormVariable('Program');
        $program = \simp\DB::Instance()->Load('Program', $this->GetParam(0));
        $program->UpdateFromArray($vars);
        \simp\DB::Instance()->Save($program);
        \Redirect(\Path::admin_program());
    }

    function Remove()
    {
        $program = \simp\DB::Instance()->Load('Program', $this->GetParam(0));
        if ($program->id > 0)
        {
            \simp\DB::Instance()->Delete($program);
        }
        \Redirect(\Path::admin_program());
    }
}
