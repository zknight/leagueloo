<?php
namespace app\admin;
class ComplexController extends \simp\Controller
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
                'delete'
            )
        );

        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);
        // tODO: check auth
    }

    function Index()
    {
        $this->StoreLocation();
        $this->complexes = \simp\Model::FindAll('Complex');
        return true;
    }

    function Add()
    {
        $this->complex = \simp\Model::Create("Complex");
        return true;
    }

    function Edit()
    {
        $this->complex = \simp\Model::FindById("Complex", $this->GetParam('id'));
        return true;
    }

    function Create()
    {
        $this->complex = \simp\Model::Create("Complex");
        $vars = $this->GetFormVariable('Complex');
        $this->complex->UpdateFromArray($vars);
        if ($this->complex->Save())
        {
            AddFlash("Complex {$complex->name} created.");
            \Redirect(\GetReturnURL());
        }
        else
        {
            $this->SetAction("add");
        }
        return true;
    }

    function Update()
    {
        $this->complex = \simp\Model::FindById("Complex", $this->GetParam('id'));
        $vars = $this->GetFormVariable('Complex');
        $this->complex->UpdateFromArray($vars);
        if ($this->complex->Save())
        {
            AddFlash("Complex {$complex->name} updated.");
            \Redirect(\GetReturnURL());
        }
        else
        {
            $this->SetAction("edit");
        }
        return true;
    }

    function Remove()
    {
        $complex = \simp\Model::FindById("Complex", $this->GetParam('id'));
        $name = $complex->name;
        if ($complex->Delete())
        {
            AddFlash("Complex $name removed.");
        }
        else
        {
            AddFlash("Invalid complex.");
        }
        \Redirect(\GetReturnURL());
    }
}
