<?php
class Sponsors extends \simp\Module
{
    protected static function OnInstall()
    {
        self::SetAdminInterface(true);
    }

    protected function Setup($args)
    {
        require_once "models/sponsor.php";
        $this->sponsors = \simp\Model::Find(
            "Sponsor", 
            "publish = ?",
            array(1)
        );
        $this->SetPermissions(
            array(
                "index" => Ability::ADMIN,
                "add" => Ability::ADMIN,
                "edit" => Ability::ADMIN,
                "delete" => Ability::ADMIN,
            )
        );
    }

    // admin methods
    public function Index()
    {
        $this->sponsors = \simp\Model::FindAll("Sponsor");
        return true;
    }

    public function Add($method, $params, $vars)
    {
        $this->sponsor = \simp\Model::Create("Sponsor");
        if ($method == \simp\Request::POST)
        {
            $v = $vars['Sponsor'];
            $v['file_info'] = $_FILES['logo'];
            $this->sponsor->UpdateFromArray($v);
            if ($this->sponsor->Save())
            {
                AddFlash("Sponsor {$this->sponsor->name} created.");
                \Redirect(\Path::module("sponsors", "admin"));
            }
        }
        return true;
    }

    public function Edit($method, $params, $vars)
    {
        $this->sponsor = \simp\Model::FindById("Sponsor", $params['id']);
        if ($method != \simp\Request::PUT)
        {
            return true;
        }
        else
        {
            $v = $vars["Sponsor"];
            $v['file_info'] = $_FILES['logo'];
            //print_r($v); exit();
            $this->sponsor->UpdateFromArray($v);
            if ($this->sponsor->Save())
            {
                AddFlash("Sponsor {$sponsor->name} updated.");
                \Redirect(\Path::module("sponsors", "admin"));
            }
        }
        return true;
    }

    public function Delete($method, $params)
    {
        if ($method == \simp\Request::DELETE)
        {
            $sponsor = \simp\Model::FindById("Sponsor", $params['id']);
            if ($sponsor->id > 0)
            {
                $name = $sponsor->name;
                $sponsor->Delete();
                AddFlash("Sponsor $name deleted.");
            }
            else
            {
                AddFlash("That sponsor is invalid.");
            }
        }
        \Redirect(\Path::module("sponsors", "admin"));
    }

}
