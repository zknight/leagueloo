<?php
namespace app;

class RescheduleController extends \app\AppController
{
    function Setup()
    {
        $this->RequireAuthorization(
            array(
                'index',
                'sumbmit'
            )
        );

        $this->MapAction('index', 'Request', \simp\Request::POST);
        $this->MapAction('submit', 'Index', \simp\Request::GET);
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
        $this->SetAction('request');
        return true;
    }
}
        
