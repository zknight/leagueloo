<?
class FieldConditions extends \simp\Module
{
    public $complexes;

    protected static function OnInstall()
    {
        global $log; $log->logDebug("FieldConditions::OnInstall()");
        self::SetAdminInterface(true);
        $log->logDebug("self::HasAdmin() returned: " . self::HasAdmin());
    }

    public function Setup($args)
    {
        //require_once "models/complex.php";
        $this->complexes = \simp\Model::FindAll("Complex");
        $this->SetPermissions(
            array(
                "index" => Ability::ADMIN,
                "add" => Ability::ADMIN,
                "edit" => Ability::ADMIN,
                "update_status" => Ability::EDIT
            )
        );
    }

    public function Status($complex)
    {
        $class = ($complex->status > 0) ? 'closed' : 'open';
        return $complex->status == 4 ? "" : "<div class=\"$class\">{$complex->status_text}</div>";
    }

    public function Time($complex)
    {
        //$time = FormatDateTime($field->updated_on, "Y/m/d H:i:s");
        $time = TimeAgoInWords($complex->updated_on);
        return "$time ago";
    }

    // admin methods
    public function Index()
    {
        $this->complex = \simp\Model::FindAll("Complex");
        return true;
    }

    public function Add($method, $params, $vars)
    {
        $this->complex = \simp\Model::Create("Complex");
        if ($method == \simp\Request::POST)
        {
            $this->complex->UpdateFromArray($vars["Complex"]);
            if ($this->complex->Save())
            {
                AddFlash("Complex {$this->complex->name} created.");
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
            $this->complex = \simp\Model::FindById("Complex", $params['id']);
            return true;
        }
        else
        {
            // update
            $complex = \simp\Model::FindById("Complex", $params['id']);
            $complex->UpdateFromArray($vars["Complex"]);
            if ($complex->Save())
            {
                AddFlash("Complex {$complex->name} updated.");
                \Redirect(\Path::module("field_conditions", "admin"));
            }
            else
            {
                $this->complex = $complex;
                return true;
            }
        }
    }

    public function UpdateStatus($method, $params, $vars)
    {
        $this->complex = \simp\Model::FindById("Complex", $params['id']);
        if ($method != \simp\Request::PUT)
        {
            return true;
        }
        else
        {
            $this->complex->UpdateFromArray($vars["Complex"]);
            if ($this->complex->Save())
            {
                AddFlash("Complex {$complex->name} updated.");
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
            $complex = \simp\Model::FindById("Complex", $params['id']);
            if ($complex->id > 0)
            {
                $name = $complex->name;
                $complex->Delete();
                AddFlash("Complex $name deleted.");
            }
            else
            {
                AddFlash("That complex is invalid.");
            }
        }
        \Redirect(\Path::module("field_conditions", "admin"));
    }

    function ShowComplex($method, $params, $vars)
    {
        $this->SetLayout('default');
        $this->complex = \simp\Model::FindById('Complex', $params['id']);
        return true;
    }


}
