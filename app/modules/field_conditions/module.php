<?
class FieldConditions extends \simp\Module
{
    public $fields;

    public function Setup()
    {
        require_once "models/field.php";
        $this->fields = \simp\Model::FindAll("Field");
        $this->SetPermissions(
            array(
                "index" => Ability::ADMIN,
                "add" => Ability::ADMIN,
                "edit" => Ability::ADMIN,
                "update_status" => Ability::EDIT
            )
        );
    }

    public function Status($field)
    {
        $class = ($field->status == 0) ? 'open' : 'closed';
        return "<span class=\"$class\">{$field->status_text}</span>";
    }

    public function Time($field)
    {
        $time = FormatDateTime($field->updated_on, "Y/m/d H:i:s");
        return "<span class=\"time\">$time</span>";
    }

    // admin methods
    public function Index()
    {
        $this->fields = \simp\Model::FindAll("Field");
        return true;
    }

    public function Add($method, $params, $vars)
    {
        $this->field = \simp\Model::Create("Field");
        if ($method == \simp\Request::POST)
        {
            $this->field->UpdateFromArray($vars["Field"]);
            if ($this->field->Save())
            {
                AddFlash("Field {$this->field->name} created.");
                \Redirect(\Path::module("field_conditions", "admin"));
            }
        }
        return true;
    }

    public function Edit($method, $params, $vars)
    {
        global $log; $log->logDebug("FieldConditions::Edit method => $method");
        if ($method != \simp\Request::PUT)
        {
            $this->field = \simp\Model::FindById("Field", $params['id']);
            return true;
        }
        else
        {
            // update
            $field = \simp\Model::FindById("Field", $params['id']);
            $field->UpdateFromArray($vars["Field"]);
            if ($field->Save())
            {
                AddFlash("Field {$field->name} updated.");
                \Redirect(\Path::module("field_conditions", "admin"));
            }
            else
            {
                $this->field = $field;
                return true;
            }
        }
    }

    public function UpdateStatus($method, $params, $vars)
    {
        $this->field = \simp\Model::FindById("Field", $params['id']);
        if ($method != \simp\Request::PUT)
        {
            return true;
        }
        else
        {
            $this->field->UpdateFromArray($vars["Field"]);
            if ($this->field->Save())
            {
                AddFlash("Field {$field->name} updated.");
                \Redirect(GetReturnURL());
            }
            else
            {
                return true;
            }
        }
    }

    public function Delete($method, $params)
    {
        if ($method == \simp\Request::DELETE)
        {
            $field = \simp\Model::FindById("Field", $params['id']);
            if ($field->id > 0)
            {
                $name = $field->name;
                $field->Delete();
                AddFlash("Field $name deleted.");
            }
            else
            {
                AddFlash("That field is invalid.");
            }
        }
        \Redirect(\Path::module("field_conditions", "admin"));
    }


}
