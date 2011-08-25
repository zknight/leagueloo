<?php
namespace app;

class RescheduleController extends \app\AppController
{
    function Setup()
    {
        $this->RequireAuthorization(
            array(
                'index',
                'submit',
                'match',
                'selectmatch',
            )
        );

        $this->MapAction('index', 'Request', \simp\Request::POST);
        $this->MapAction('match', 'Index', \simp\Request::GET);
        $this->MapAction('selectmatch', 'Index', \simp\Request::GET);
    }

    function Index()
    {
        $this->StoreLocation();
        return true;
    }

    function Request()
    {
        $resched = $this->GetFormVariable('resched');
        $this->affirmed = $resched['affirm'];
        if (!$this->affirmed)
        {
            AddFlash("You must affirm the Reschedule Agreement before proceding with a reschedule request.");
            \Redirect(\GetReturnURL());
        }
        $this->reschedule = \simp\Model::Create("Reschedule");
        $this->user = $this->GetUser();
        $this->reschedule->requestor_name = "{$this->user->first_name} {$this->user->last_name}";
        $this->reschedule->requestor_email = $this->user->email; 
        $this->SetAction('request');
        return true;
    }

    function Match()
    {
        $vars = $this->GetFormVariable('Reschedule');
        $this->reschedule = \simp\Model::Create("Reschedule");
        $this->reschedule->UpdateFromArray($vars);
        $this->reschedule->step = 1;
        if (!$this->reschedule->affirmed)
        {
            AddFlash("You must affirm the Reschedule Agreement before proceding with a reschedule request.");
            \Redirect(\GetReturnURL());
        }

        if (!$this->reschedule->Save())
        {
            $this->SetAction('request');
        }

        $this->matches = \simp\Model::Find(
            "Match", 
            "date = ? and division = ? and age = ? and gender = ?",
            array(
                $this->reschedule->orig_date, 
                $this->reschedule->division, 
                $this->reschedule->age, 
                $this->reschedule->gender
            )
        );
        
        return true;
    }

    function Selectmatch()
    {
        //print_r($this->_form_vars); return false;
        $vars = $this->GetFormVariable('Reschedule');
        $this->reschedule = \simp\Model::FindById("Reschedule", $this->GetParam('id'));
        $this->reschedule->UpdateFromArray($vars);
        $this->reschedule->step = 2;

        if (!$this->reschedule->Save())
        {
            $this->matches = \simp\Model::Find(
                "Match", 
                "date = ? and division = ? and age = ? and gender = ?",
                array(
                    $this->reschedule->orig_date, 
                    $this->reschedule->division, 
                    $this->reschedule->age, 
                    $this->reschedule->gender
                )
            );
            $this->SetAction("match");
        }

        return true;
    }
}
        
