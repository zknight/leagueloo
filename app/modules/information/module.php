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
        $this->SetPermissions(
            array(
                "index" => Ability::ADMIN,
                "add_email" => Ability::ADMIN,
                "add_category" => Ability::ADMIN,
                "edit_category" => Ability::ADMIN,
                "remove_datum" => Ability::ADMIN
            )
        );
    }

    public function Index()
    {
        $this->categories = \simp\Model::FindAll("Category");
        //$this->general = \simp\Model::Find("SubCategory", "category = ?", array(Email::GENERAL));
        //$this->program = \simp\Model::Find("SubCategory", "category = ?", array(Email::PROGRAM));
        //$this->website = \simp\Model::Find("SubCategory", "category = ?", array(Email::WEBSITE));
        return true;
    }

    public function AddEmail($method, $params, $vars)
    {
        $this->email = \simp\Model::Create("Email");
        $this->category = \simp\Model::FindById("Category", $params['id']);
        if ($method == \simp\Request::POST)
        {
            $this->email->UpdateFromArray($vars['Email']);
            {
                if ($this->email->Save())
                {
                    AddFlash("Site Email {$this->email->type} created.");
                    \Redirect(Path::module('information', 'admin'));
                }
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

    public function AddCategory($method, $params, $vars)
    {
        $this->category = \simp\Model::Create("Category");
        $this->datum = \simp\Model::Create("Datum");

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
        global $log; 
        $log->logDebug("EditCategory() $vars = " . print_r($vars, true));

        if ($method == \simp\Request::PUT)
        {
            if (isset($vars['add_datum']))
            {
                $this->datum->UpdateFromArray($vars['Datum']);
                $this->datum->category_id = $this->category->id;
                if ($this->datum->Save())
                {
                    $this->category->data[$this->datum->id] = $this->datum;
                    $this->category->UpdateFromArray($vars['Category']);
                    $this->category->Save();
                }
            }
            else 
            {
                $this->category->UpdateFromArray($vars['Category']);
                if ($this->category->Save())
                {
                    AddFlash("Category {$this->category->name} updated.");
                    \Redirect(Path::module('information', 'admin'));
                }
            }
        }
        return true;
    }


    public function AddDatum($type, $id)
    {

        $this->datum = \simp\Model::Create("Datum");
        if ($method == \simp\Request::POST)
        {

        }

    }

    public function RemoveDatum($method, $params, $vars)
    {
        $datum = \simp\Model::FindById('Datum', $params['id']);
        if ($datum->id > 0)
            $datum->Delete();
        Redirect(GetReturnURL());
    }
    /*
    public function RemoveSub($method, $params, $vars)
    {
        if ($method == \simp\Request::DELETE)
        {
            $this->sub_category = \simp\Model::FindById('SubCategory', $params['id']);
            if ($this->sub_category->id > 0)
            {
                $this->sub_category->Delete();
            }
        }
        Redirect(GetReturnURL());
    }
     */
}
