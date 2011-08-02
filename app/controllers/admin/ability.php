<? namespace app\admin;
// TODO: rethink this
class AbilityController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            array(
                'index',
                'add',
            )
        );

        $this->MapAction("add", "Create", \simp\Request::POST);
    }

    function Add()
    {
        $offset = $this->GetParam('u');
        $offset = $offset == NULL ? 0 : $offset;
        $this->per_page = 32;
        $this->entity_type = $this->GetParam('entity');
        $this->entity_id = $this->GetParam('entity_id');
        if (!$this->GetUser()->CanAdmin($this->entity_type, $this->entity_id))
        {
            \Redirect(GetReturnURL());
        }
        if ($this->entity_type == NULL || $this->entity_id == NULL)
        {
            AddFlash("No such entity.");
            \Redirect(GetReturnURL());
        }
        $ucount = \R::count('user');
        $this->pages = array();
        for ($i = 0; $i < $ucount; $i += $this->per_page)
        {
            $this->pages[] = "u=$i";
        }
        $users = \simp\Model::Find('User', "1 order by first_name limit {$this->per_page} offset $offset", array());
        $this->users = array();
        foreach ($users as $user)
        {
            $this->users[$user->id] = "{$user->first_name} {$user->last_name}";
        }
        $this->entity = \simp\Model::FindById($this->entity_type, $this->entity_id);
        $this->ability = \simp\Model::Create('Ability');
        $this->cur_page = $offset/$this->per_page;
        return true;
    }

    function Edit()
    {
        return true;
    }

    function Create()
    {
        $vars = $this->GetFormVariable('Ability');
        $ability = \simp\Model::Create('Ability');
        $ability->UpdateFromArray($vars);
        $ability->Save();
        Redirect(GetReturnURL());
    }

    function Remove()
    {
        $entity_id = $this->GetParam('entity_id');
        $entity = $this->GetParam('entity');
        if (!$this->GetUser()->CanAdmin($entity, $entity_id))
        {
            \Redirect(GetReturnURL());
        }
        $uid = $this->GetParam('uid');
        \R::debug(true);
        $ability = \simp\Model::FindOne(
            'Ability', 
            "entity_id = ? and entity_type = ? and user_id = ?",
            array($entity_id, $entity, $uid)
        );
        \R::debug(false);

        if ($ability->id > 0)
        {
            $ability->Delete();
            AddFlash("Privilege removed.");
        }
        Redirect(GetReturnURL());
    }
}
