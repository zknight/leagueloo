<?
namespace app\admin;
class FieldsController extends \simp\Controller
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
                'blackout',
                'delblackout',
                )
            );

        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);
        $this->MapAction("blackout", "AddBlackout", \simp\Request::POST);
        $this->MapAction("delblackout", "RemoveBlackout", \simp\Request::DELETE);

    }

    function Index()
    {
        $this->StoreLocation();
        $this->complexes = \simp\Model::FindAll('Complex');
        return true;
    }

    function Show()
    {
        $this->StoreLocation();
        $this->field = \simp\Model::FindById('Field', $this->GetParam('id'));
        if ($this->field->id > 0)
        {
            return true;
        }
        else
        {
            \Redirect(\GetReturnURL());
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
            \Redirect(\GetReturnURL());
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
            \Redirect(\GetReturnURL());
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
            \Redirect(\GetReturnURL());
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
        \Redirect(\GetReturnURL());
    }

    public function Blackout()
    {
        $this->blackout = \simp\Model::Create("Blackout");
        $etype = $this->GetParam("entity");
        $eid = $this->GetParam("entity_id");
        $this->field_opts = array();
        if ($etype == "field")
        {
            $f = \simp\Model::FindById("Field", $eid);
            $this->field_opts[$f->id] = $f->name;
            $this->complex = $f->complex;
        }
        else if ($etype == "complex")
        {
            $this->complex = \simp\Model::FindById("Complex", $eid);
            $this->field_opts[0] = "All Fields at this complex";
            $fs = \simp\Model::Find("Field", "complex_id = ? order by name", array($eid));
            foreach ($fs as $f)
            {
                $this->field_opts[$f->id] = $f->name;
            }
        }
        else
        {
            AddFlash("Invalid request.");
            \Redirect(\GetReturnURL());
        }
        return true;

    }

    public function AddBlackout()
    {
        $vars = $this->GetFormVariable('Blackout');
        $field_id = $vars['field_id'];
        if ($field_id == 0)
        {
            $fs = \simp\Model::Find("Field", "complex_id = ?", array($vars['complex_id']));
            unset($vars['complex_id']);
            foreach ($fs as $f)
            {
                $this->blackout = \simp\Model::Create("Blackout");
                $vars['field_id'] = $f->id;
                $this->blackout->UpdateFromArray($vars);
                if (!$this->blackout->Save())
                {
                    $this->SetAction("blackout");
                    return true;
                }
            }
        }
        else
        {
            $this->blackout = \simp\Model::Create('Blackout');
            unset($vars['complex_id']);
            $this->blackout->UpdateFromArray($vars);
            if (!$this->blackout->Save())
            {
                $this->SetAction("blackout");
                return true;
            }
        }
        AddFlash("Blackout saved.");
        \Redirect(\GetReturnURL());
    }

    public function RemoveBlackout()
    {
        $etype = $this->GetParam("entity");
        $eid = $this->GetParam("entity_id");
        $count = 0;
        switch ($etype)
        {
        case "blackout":
            $b = \simp\Model::FindById("Blackout", $eid);
            $b->Delete();
            $count++;
            break;
        case "field":
            $bs = \simp\Model::Find("Blackout", "field_id = ?", array($eid));
            foreach ($bs as $b)
            {
                $b->Delete();
                $count++;
            }
            break;
        case "complex":
            $fs = \simp\Model::Find("Field", "complex_id = ?", array($eid));
            foreach ($fs as $f)
            {
                foreach ($f->blackouts as $b)
                {
                    $b->Delete();
                    $count++;
                }
            }
            break;
        }

        AddFlash("$count Blackout Dates removed.");
        \Redirect(\GetReturnURL());

    }

    public function GetComplexOpts()
    {
        $opts = array();
        $complexes = \simp\Model::FindAll("Complex");
        foreach ($complexes as $complex)
        {
            $opts[$complex->id] = $complex->name;
        }
        return $opts;
    }
}
