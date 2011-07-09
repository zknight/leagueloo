<?php
class Information extends \simp\Module
{
    protected static function OnInstall()
    {
        self::SetAdminInterface(true);
    }

    protected function Setup($args)
    {
        require_once "models/email.php";
        require_once "models/info.php";
        $this->SetPermissions(
            array(
                "index" => Ability::ADMIN,
                "add_email" => Ability::ADMIN,
                "edit_email" => Ability::ADMIN,
                "remove_email" => Ability::ADMIN,
                "add_category" => Ability::ADMIN,
                "edit_category" => Ability::ADMIN,
                "remove_category" => Ability::ADMIN,
                "add_info" => Ability::ADMIN,
                "edit_info" => Ability::ADMIN,
                "remove_info" => Ability::ADMIN,
                "remove_datum" => Ability::ADMIN
            )
        );
        $this->categories = \simp\Model::FindAll("Category");
        $this->info = \simp\Model::Find("Info", "1 order by weight asc", array());
    }

    /////////  Admin actions
    public function Index()
    {
        //$this->categories = \simp\Model::FindAll("Category");
        //$this->info = \simp\Model::Find("Info", "1 order by weight asc", array());
        //$this->general = \simp\Model::Find("SubCategory", "category = ?", array(Email::GENERAL));
        //$this->program = \simp\Model::Find("SubCategory", "category = ?", array(Email::PROGRAM));
        //$this->website = \simp\Model::Find("SubCategory", "category = ?", array(Email::WEBSITE));
        return true;
    }

    public function AddInfo($method, $params, $vars)
    {
        $this->item = \simp\Model::Create("Info");
        if ($method == \simp\Request::POST)
        {
            $this->item->UpdateFromArray($vars['Info']);
            if ($this->item->Save())
            {
                AddFlash("Info item {$this->item->label} created.");
                \Redirect(Path::module('information', 'admin'));
            }
        }
        return true;
    }

    public function EditInfo($method, $params, $vars)
    {
        $this->item = \simp\Model::FindById("Info", $params['id']);
        if ($method == \simp\Request::PUT)
        {
            $this->item->UpdateFromArray($vars['Info']);
            if ($this->item->Save())
            {
                AddFlash("Info item {$this->item->label} updated.");
                \Redirect(Path::module('information', 'admin'));
            }
        }
        return true;
    }

    public function RemoveInfo($method, $params, $vars)
    {
        $item = \simp\Model::FindById("Info", $params['id']);
        if ($method == \simp\Request::DELETE && $item->id > 0)
        {
            $item->Delete();
        }
        \Redirect(Path::module('information', 'admin'));
    }

    public function AddEmail($method, $params, $vars)
    {
        $this->email = \simp\Model::Create("Email");
        $this->category = \simp\Model::FindById("Category", $params['id']);
        if ($method == \simp\Request::POST)
        {
            $this->email->UpdateFromArray($vars['Email']);
            if ($this->email->Save())
            {
                AddFlash("Site Email {$this->email->address} created.");
                \Redirect(Path::module('information', 'admin'));
            }
        }
        else
        {
            if ($this->category->id < 1)
            {
                AddFlash("Category not specified.  How did you get here?");
                \Redirect(Path::module('information', 'admin'));
            }
        }
        return true;
    }

    public function EditEmail($method, $params, $vars)
    {
        $this->email = \simp\Model::FindById("Email", $params['id']);
        if ($method == \simp\Request::PUT)
        {
            $this->email->UpdateFromArray($vars['Email']);
            if ($this->email->Save())
            {
                AddFlash("Site Email {$this->email->address} updated.");
                \Redirect(Path::module('information', 'admin'));
            }
        }
        return true;
    }

    public function RemoveEmail($method, $params, $vars)
    {
        $email = \simp\Model::FindById("Email", $params['id']);
        if ($method == \simp\Request::DELETE && $email->id > 0)
        {
            $email->Delete();
        }
        \Redirect(Path::module('information', 'admin'));
    }

    public function AddCategory($method, $params, $vars)
    {
        $this->category = \simp\Model::Create("Category");

        if ($method == \simp\Request::POST)
        {
            $this->category->UpdateFromArray($vars['Category']);
            if ($this->category->Save())
            {
                AddFlash("Category {$this->category->name} created.");
                \Redirect(Path::module('information', 'admin'));
            }
        }
        return true;
    }

    public function EditCategory($method, $params, $vars)
    {
        $this->category = \simp\Model::FindById('Category', $params['id']);

        if ($method == \simp\Request::PUT)
        {
            $this->category->UpdateFromArray($vars['Category']);
            if ($this->category->Save())
            {
                AddFlash("Category {$this->category->name} updated.");
                \Redirect(Path::module('information', 'admin'));
            }
        }
        return true;
    }

    public function RemoveCategory($method, $params, $vars)
    {
        $category = \simp\Model::FindById("Category", $params['id']);
        if ($method == \simp\Request::DELETE && $category->id > 0)
        {
            $category->Delete();
        }
        \Redirect(Path::module('information', 'admin'));
    }

    public function RemoveDatum($method, $params, $vars)
    {
        $datum = \simp\Model::FindById('Datum', $params['id']);
        if ($datum->id > 0)
            $datum->Delete();
        Redirect(GetReturnURL());
    }

    //////// User actions
    public function ShowForm($method, $params, $vars)
    {
        require_once "models/info_request.php";
        $this->SetLayout('default');
        $this->category = \simp\Model::FindById('Category', $params['id']);
        $this->info_request = new InfoRequest();
        $this->info_request->AddFields($this->category->data);
        return true;
    }
}
