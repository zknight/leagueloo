<?php
namespace app\admin;
class RescheduleController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            array(
                'index',
                'configure',
                'delete',
                'email',
                'show',
                'accept',
                'deny',
            )
        );

        $this->MapAction('configure', 'Update', \simp\Request::PUT);
        $this->MapAction('delete', 'DelEmail', \simp\Request::GET);
    }

    public function Index()
    {
        $configured = $this->LoadVariable('resched:configured', false);
        if ($configured->value != true)
        {
            \Redirect(\Path::admin_reschedule_configure());
        }
        $this->pending = \simp\Model::Find("Reschedule", "state = ?", array(\Reschedule::PENDING));
        $this->approved = \simp\Model::Find("Reschedule", "state = ?", array(\Reschedule::APPROVED));

        return true;
    }

    public function Configure()
    {
        $this->LoadEmails();
        return true;
    }

    public function Update()
    {
        $this->LoadEmails();
        /*
        echo "<pre>";
        print_r($this->_form_vars);
        echo "</pre>";
         */
        $button = $this->GetFormVariable('button');
        switch ($button)
        {
        case "Add Scheduler":
            $sched = $this->GetFormVariable('scheduler');
            if ($sched['name'] != "" && $sched['email'] != "")
            {
                $this->schedulers[] = $sched;
                SetCfgVar('resched:schedulers', serialize($this->schedulers));
                SetCfgVar('resched:configured', true);
            }
            break;
        case "Add Referee Coordinator":
            $coord = $this->GetFormVariable('ref_coord');
            if ($coord['name'] != "" && $coord['email'] != "")
            {
                $this->ref_coordinators[] = $coord;
                SetCfgVar('resched:ref_coordinators', serialize($this->ref_coordinators));
            }
            break;
        case "Add CC Recipient":
            $cc = $this->GetFormVariable('cc_email');
            if ($cc['name'] != "" && $cc['email'] != "")
            {
                $this->cc_emails[] = $coord;
                SetCfgVar('resched:cc_emails', serialize($this->cc_emails));
            }
            break;
        }
        
        \Redirect(\Path::admin_reschedule_configure());
    }

    public function DelEmail()
    {
        $type = $this->GetParam('entity');
        $idx = $this->GetParam('entity_id');
        $this->LoadEmails();
        switch ($type)
        {
        case "sched":
            unset($this->schedulers[$idx]);
            $this->schedulers = array_values($this->schedulers);
            SetCfgVar("resched:schedulers", serialize($this->schedulers));
            break;
        case "coord":
            unset($this->ref_coordinators[$idx]);
            $this->ref_coordinators = array_values($this->ref_coordinators);
            SetCfgVar("resched:ref_coordinators", serialize($this->ref_coordinators));
            break;
        case "sched":
            unset($this->cc_emails[$idx]);
            $this->cc_emails = array_values($this->cc_emails);
            SetCfgVar("resched:cc_emails", serialize($this->cc_emails));
            break;
        }
        \Redirect(\Path::admin_reschedule_configure());
    }

    protected function LoadEmails()
    {
        $def = serialize(array());
        $this->schedulers = unserialize(GetCfgVar('resched:schedulers', $def));
        $this->ref_coordinators = unserialize(GetCfgVar('resched:ref_coordinators', $def));
        $this->cc_emails = unserialize(GetCfgVar('resched:cc_emails', $def));
    }
}
