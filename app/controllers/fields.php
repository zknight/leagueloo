<?php
namespace app;

class FieldsController extends \app\AppController
{
    function Setup()
    {
        $this->MapAction('index', 'Show', \simp\Request::POST);
    }

    function Index()
    {
        $this->StoreLocation();
        $this->info = new \Info(
            'date' => \Info::DATE,
            'division' => \Info::TEXT,
        );

        return true;
    }

    function Show()
    {
        $vars = $this->GetFormVariable('Info');
        $this->info = new \Info(
            'date' => \Info::DATE,
            'division' => \Info::TEXT,
        );
        $this->info->UpdateFromArray('vars');
        if (!$this->info->Save())
        {
            $this->SetAction('index');
        }
        else
        {
            $this->division = \simp\Model::FindOne('Division', 'name = ?', array($vars['division'])); 
            $this->date = $vars['date'];
        }
        return true;
    }

}
