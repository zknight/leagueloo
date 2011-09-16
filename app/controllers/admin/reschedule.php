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
                'fields',
                'email',
                'show',
                'accept',
                'deny',
            )
        );

        $this->MapAction('configure', 'Update', \simp\Request::PUT);
        $this->MapAction('delete', 'DelEmail', \simp\Request::GET);
        $this->MapAction('accept', 'DoAccept', \simp\Request::PUT);
        $this->MapAction('deny', 'DoDeny', \simp\Request::PUT);
    }

    public function Index()
    {
        $configured = $this->LoadVariable('resched:configured', false);
        if ($configured->value != true)
        {
            \Redirect(\Path::admin_reschedule_configure());
        }
        $this->StoreLocation();
        $this->pending = \simp\Model::Find("Reschedule", "state = ? order by updated_at asc", array(\Reschedule::PENDING));
        $this->approved = \simp\Model::Find("Reschedule", "state = ? order by updated_at asc", array(\Reschedule::APPROVED));
        $this->denied = \simp\Model::Find("Reschedule", "state = ? order by updated_at asc", array(\Reschedule::DENIED));

        return true;
    }

    public function Show()
    {
        $this->StoreLocation();
        $this->request = \simp\Model::FindById("Reschedule", $this->GetParam("id"));
        return true;
    }

    public function Accept()
    {
        $this->request = \simp\Model::FindById("Reschedule", $this->GetParam("id"));
        $division = \simp\Model::FindById("Division", $this->request->division_id);
        $this->fields = array();
        foreach ($division->fields as $field)
        {
            $this->fields[$field->id] = $field->name;
        }
        return true;
    }

    public function DoAccept()
    {
        $this->request = \simp\Model::FindById("Reschedule", $this->GetParam("id"));
        $vars = $this->GetFormVariable("Reschedule");
        $this->request->UpdateFromArray($vars);
        $this->request->state = \Reschedule::APPROVED;
        if (!$this->request->Save())
        {
            $this->SetAction("accept");
            $division = \simp\Model::FindById("Division", $this->request->division_id);
            $this->fields = array();
            foreach ($division->fields as $field)
            {
                $this->fields[$field->id] = $field->name;
            }
        }
        else
        {
            // find the match and update it
            $match = \simp\Model::FindById("Match", $this->request->match_id);
            $match->start_time = $this->request->new_start_time;
            $match->end_time = $this->request->new_end_time;
            $match->date_str = $this->request->new_date_str;
            $match->field_id = $this->request->field_id;
            if (!$match->Save())
            {
                AddFlash("Something went wrong.  Tell Zayne to fix it.");
                \Redirect(\GetReturnURL());
            }
            $this->request->SendAcceptEmail();
            AddFlash("Reschedule request has been approved.");
            \Redirect(\GetReturnURL());
        }

        return true;
    }

    public function Deny()
    {
        $this->request = \simp\Model::FindById("Reschedule", $this->GetParam("id"));
        return true;
    }

    public function DoDeny()
    {
        $this->request = \simp\Model::FindById("Reschedule", $this->GetParam("id"));
        $vars = $this->GetFormVariable("Reschedule");
        $this->request->UpdateFromArray($vars);
        $this->request->state = \Reschedule::DENIED;
        if (!$this->request->Save())
        {
            $this->SetAction("deny");
        }
        else
        {
            $this->request->SendDenyEmail();
            AddFlash("Reschedule request denied.");
            \Redirect(\GetReturnURL());
        }

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

    public function Fields()
    {

    }

    protected function LoadEmails()
    {
        $def = serialize(array());
        $this->schedulers = unserialize(GetCfgVar('resched:schedulers', $def));
        $this->ref_coordinators = unserialize(GetCfgVar('resched:ref_coordinators', $def));
        $this->cc_emails = unserialize(GetCfgVar('resched:cc_emails', $def));
    }
}
