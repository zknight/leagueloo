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
                'selectfield',
                'submit',
                'feerequired',
            )
        );

        $this->MapAction('index', 'Request', \simp\Request::POST);
        $this->MapAction('match', 'Index', \simp\Request::GET);
        $this->MapAction('selectfield', 'Index', \simp\Request::GET);
        $this->MapAction('submit', 'Index', \simp\Request::GET);
        //$this->MapAction('validate', 'ValidatePost', \simp\Request::POST);
    }

    function Index()
    {
        $this->StoreLocation();
        $now = new \DateTime("now");
        $q = "select id, name from schedule where end_date >= ? and allow_reschedules = ?";
        $scheds = \R::getAll($q, array($now->getTimestamp(), true));
        $this->schedopts = array(0 => 'PLEASE SELECT');
        foreach ($scheds as $vals)
        {
            $this->schedopts[$vals['id']] = $vals['name'];
        }

        return true;
    }

    function Request()
    {
        $resched = $this->GetFormVariable('resched');
        $this->affirmed = $resched['affirm'];
        $this->schedule_id = $resched['schedule'];
        if (!$this->affirmed)
        {
            AddFlash("You must affirm the Reschedule Agreement before proceding with a reschedule request.");
            \Redirect(\GetReturnURL());
        }
        if ($this->schedule_id == 0)
        {
            AddFlash("You must select a Match Schedule before proceding with a reschedule request.");
            \Redirect(\GetReturnURL());
        }
        $this->reschedule = \simp\Model::Create("Reschedule");
        $this->user = $this->GetUser();
        $this->reschedule->requestor_name = "{$this->user->first_name} {$this->user->last_name}";
        $this->reschedule->requestor_email = $this->user->email; 
        $this->reschedule->schedule_id = $this->schedule_id;
        $this->SetAction('request');
        return true;
    }

    function Match()
    {
        $vars = $this->GetFormVariable('Reschedule');
        $this->reschedule = \simp\Model::Create("Reschedule");
        $this->reschedule->UpdateFromArray($vars);
        $div = \simp\Model::FindById("Division", $this->reschedule->division_id);
        $deadlines = unserialize(GetCfgVar('resched:deadlines'));
        $format = $div->format;
        //print_r($deadlines);

        /*
        $days_before = 6 - $deadlines[$format]['day'];
        
        if ($days_before < 1) $days_before += 7;
        echo "days before = $days_before";
        $now = new \DateTime(now);
        echo " original: {$this->reschedule->orig_date_str}";
        echo " format = {$div->format}";
        $game_date = new \DateTime("{$this->reschedule->orig_date_str} 5:00 PM");
        $interval = $now->diff($game_date);
        print_r($interval);
        $this->reschedule->fee_required = false;
        if ($interval->d < $days_before)
         */
        $now = new \DateTime(now);
        $deadline = $this->GetDeadline($deadlines[$format]['day'], $this->reschedule->orig_date_str);
        $this->reschedule->deadline = $deadline->format("D m/d/Y g:i A");
        if ($now->getTimestamp() > $deadline->getTimestamp())
        {
            $this->reschedule->fee_required = true;
            $this->reschedule->fee = $deadlines[$format]['amount'];
        }

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

        $this->affirmed = $this->reschedule->affirmed;
        $this->games = \simp\Model::Find(
            "Game", 
            "date = ? and division_id = ?", // and age = ? and gender = ?",
            array(
                $this->reschedule->orig_date, 
                $this->reschedule->division_id, 
                //$this->reschedule->age, 
                //$this->reschedule->gender
            )
        );
        
        return true;
    }

    function Selectfield()
    {
        //print_r($this->_form_vars); return false;
        $vars = $this->GetFormVariable('Reschedule');
        $this->reschedule = \simp\Model::FindById("Reschedule", $this->GetParam('id'));
        $this->reschedule->UpdateFromArray($vars);
        $this->reschedule->step = 2;

        if ($this->reschedule->fee_required && !$this->reschedule->pay_fee)
        {
            AddFlash("You must agree to pay the referee fee or this game cannot be rescheduled.");
            $this->games = \simp\Model::Find(
                "Game", 
                "date = ? and division_id = ?", // and age = ? and gender = ?",
                array(
                    $this->reschedule->orig_date, 
                    $this->reschedule->division_id, 
                    //$this->reschedule->age, 
                    //$this->reschedule->gender
                )
            );
            $this->SetAction("match");
        }

        if (!$this->reschedule->Save())
        {
            $this->games = \simp\Model::Find(
                "Game", 
                "date = ? and division_id = ?", // and age = ? and gender = ?",
                array(
                    $this->reschedule->orig_date, 
                    $this->reschedule->division_id, 
                    //$this->reschedule->age, 
                    //$this->reschedule->gender
                )
            );
            $this->SetAction("match");
        }
        else
        {
            //$this->schedule = \simp\Model::Create('Schedule');
            $this->division = \simp\Model::FindById('Division', $this->reschedule->division_id);
        }

        return true;
    }

    public function ValidatePost()
    {
        $token = $this->GetFormVariable('token');
        $id = $this->GetParam('id');
        \Redirect("/reschedule/validate/{$id}/$token");
    }

    public function Validate()
    {
        $this->show_form = false;
        $this->message = '';
        $token = "";
        $id = $this->GetParam('id'); 
        if ($this->_method == \simp\Request::POST)
        {
            $token = $this->GetFormVariable('token');
        }
        else
        {
            $token = $this->GetParam('token');
            global $log;
            $log->logDebug("checking token {$token}");
        }
        $this->reschedule = \simp\Model::FindById("Reschedule", $id);
        if (!$this->reschedule)
        {
            \AddFlash("Invalid reschedule request.");
            \Redirect(\GetReturnURL());
        }
        if (!$token)
        {
            $this->show_form = true;
        }
        elseif ($token == $this->reschedule->verification_string)
        {
            $this->reschedule->state = \Reschedule::VERIFIED;
            if ($this->reschedule->Save())
            {
                $this->reschedule->SendRequestEmail();
                AddFlash("Requst has been validated.");
            }
        }
        else
        {
            global $log;
            $log->logDebug("did not match {$this->reschedule->verification_string}");
            $email = GetCfgVar('site_email');
            $this->message = "The token you entered does not match.  Please double-check your email and try again. ";
            $this->message .= "If you require further assistance, please contact the site administrator: ";
            $this->message .= "<a href='mailto:{$email}'>{$email}</a>";
            $this->show_form = true;
        }
        return true;
    }

    public function Submit()
    {
        $this->reschedule = \simp\Model::FindById("Reschedule", $this->GetParam('id'));
        $vars = $this->GetFormVariable('Reschedule');
        $this->reschedule->UpdateFromArray($vars);
        $this->reschedule->state = \Reschedule::PENDING;
        $this->reschedule->step = 3;
        if ($this->reschedule->Save())
        {
            $this->reschedule->SendVerifyEmail();
        }
        else 
        {
            $this->division = \simp\Model::FindById('Division', $this->reschedule->division_id);
            $this->SetAction("selectfield");
        }
        return true;
    }

    protected function GetDeadline($dead_day, $game)
    {
        echo "<pre>";
        echo "date: $game\n";
        echo "dead day: $dead_day\n";
        $game_date = new \DateTime($game);
        $dow = $game_date->format("w");
        echo "dow: $dow\n";
        $x = 7- (($dow + 1) % 7);
        $int = 13 - $dead_day - $x;
        echo "x: $x\n";
        echo "int: $int\n";
        $deaddate = new \DateTime($game);
        $deaddate->modify("-{$int} day");
        $deadline = new \DateTime($deaddate->format("m/d/Y") . " 5:00PM");
        echo "</pre>";
        return $deadline;
    }
}
        
