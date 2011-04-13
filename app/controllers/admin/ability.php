<? namespace app\admin;
// TODO: rethink this
class AbilityController extends \simp\Controller
{
    function Setup()
    {
        $this->AddAction('add', \simp\Request::GET, 'Add');
        $this->AddAction('add', \simp\Request::POST, 'Create');
        $this->AddAction('edit', \simp\Request::GET, 'Edit');
        $this->AddAction('edit', \simp\Request::PUT, 'Update');
        $this->AddAction('delete', \simp\Request::DELETE, 'Remove');
    }

    function Add()
    {
        $this->user_id = $this->GetParam(0);
        $this->ability = \simp\DB::Instance()->Create('Ability');
        return true;
    }

    function Edit()
    {
        return true;
    }

    function Create()
    {
        $vars = $this->GetFormVariable('Ability');
        $ability = \simp\DB::Instance()->Create('Ability');
        $user->UpdateFromArray($vars);
        \simp\DB::Instance()->Save($ability);
        Redirect(\Path::admin_user_add($ability->user_id));
    }
}
