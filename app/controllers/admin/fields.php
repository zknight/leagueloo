<?
namespace app\admin;
class FieldsController extends \simp\Controller
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
        $this->MapAction("edit", "Update", \simp\Request::POST);
        $this->MapAction("delete", "Remove", \simp\Request::POST);

    }

    function Index()
    {
        $this->fields = \simp\Model::FindAll('Field');
        return true;
    }

    function Show()
    {
        $this->field = \simp\Model::FindById('Field', $this->GetParam('id'));
        if ($this->field->id > 0)
        {
            return true;
        }
        else
        {
            \Redirect(\Path::admin_fields());
        }
    }

    function Add()
    {
        $this->field = \simp\Model::Create('Field');
        return true;
    }

    function Edit()
    {
        $this->field = \simp\Model::FindById('Field', $this->GetParam('id'));
        if ($this->field->id > 0)
        {
            return true;
        }
        else
        {
            AddFlash("That field doesn't exist.");
            \Redirect(\Path::admin_field);
        }
    }

    function Create()
    {
        $vars = $this->GetFormVariable('Field');
        $field = \simp\Model::Create('Field');
        $field->UpdateFromArray($vars);
        if ($field->Save())
        {
            AddFlash("Field {$field->name} Created.");
            \Redirect(\Path::admin_fields());
        }
        else
        {
            $this->field = $field;
            $this->Render('Add');
            return false;
        }
    }

    function Update()
    {
        $vars = $this->GetFormVariable('Field');
        $field = \simp\Model::FindById('Field', $this->GetParam('id'));
        $field->UpdateFromArray($vars);
        if ($field->Save())
        {
            AddFlash("Field {$field->name} Updated.");
            \Redirect(\Path::admin_fields());
        }
        else
        {
            $this->field = $field;
            $this->Render('Edit');
            return false;
        }
    }

    public function Remove()
    {
        $id = $this->GetParam('id');
        $field = \simp\Model::FindById('Field', $id);
        if ($field->id > 0)
        {
            $name = $field->name;
            $field->Delete();
            AddFlash("Field $name deleted.");
        }
        else
        {
            AddFlash("That field is invalid.  Please contact the site administrator.");
        }
        \Redirect(\Path::admin_fields());
    }
}
