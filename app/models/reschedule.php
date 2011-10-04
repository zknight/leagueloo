<?php
class Reschedule extends \simp\Model
{
    const PENDING = 0;
    const APPROVED = 1;
    const DENIED = 2;

    const MORNING = 1;
    const AFTERNOON = 2;

    public static $tod_opts = array(
        self::MORNING => "Morning (before Noon)",
        self::AFTERNOON => "Afternoon (prior to 7:00p)",
    );

    public static $approval_state = array(
        self::PENDING => "Pending",
        self::APPROVED => "Approved",
        self::DENIED => "Denied"
    );

    public static $reschedule_reasons = array(
        "At Tournament",
        "Coach Conflict",
        "Complex Closed",
        "See Comment"
    );

    protected $_schedule;
    protected $_game;
    protected $_division;
    public $step;
    public $orig_date_str;
    public $first_choice_str;
    public $second_choice_str;
    public $new_date_str;

    public function Setup()
    {
    }

    public function OnLoad()
    {
        $this->orig_date_str = FormatDateTime($this->orig_date, "m/d/Y");
        $this->first_choice_str = FormatDateTime($this->first_choice, "m/d/Y");
        $this->second_choice_str = FormatDateTime($this->second_choice, "m/d/Y");
        $this->new_date_str = FormatDateTime($this->new_date, "m/d/Y");
    }

    public function __get($property)
    {
        switch ($property)
        {
        case 'schedule':
            if (empty($this->_schedule))
            {
                //$q = "select distinct division, age, gender from game where schedule_id = ? order by division, age, gender;";
                //$this->_divisions = \R::getAll($q, $this->schedule_id);
                $this->_schedule = \simp\Model::FindById("Schedule", $this->schedule_id);
            }
            return $this->_schedule;
            break;
        case 'game':
            if (empty($this->_game))
            {
                $this->_game = \simp\Model::FindById("Game", $this->game_id);
            }
            return $this->_game;
            break;
        case 'division':
            if (empty($this->_division) and $this->id > 0)
            {
                $this->_division = \simp\Model::FindById("Division", $this->division_id);
            }
            return $this->_division;
        default:
            return parent::__get($property);
        }
    }

    public function __set($property, $val)
    {
        switch($property)
        {
        default:
            parent::__set($property, $val);
            break;
        }
    }

    public function DivisionOpts()
    {
        $sched = $this->schedule;
        $divs = $sched->divisions;
        $opts = array();
        foreach ($divs as $div)
        {
            $opts[$div->id] = $div->name;
        }
        return $opts;
    }

    public function BeforeSave()
    {
        if ($this->step == 1)
        {
            $this->VerifyEmail('opponent_email');
            $this->VerifyNotEmpty('opponent_name');
            $this->VerifyValidDate('orig_date_str');
            $this->orig_date = strtotime($this->orig_date_str);
        }
        if ($this->step == 2)
        {
            $this->VerifyNotEmpty('game_id');
            $this->VerifyNotEqual('game_id', 0);
            $this->VerifyValidDate('first_choice_str');
            $this->VerifyValidDate('second_choice_str');
            $this->first_choice = strtotime($this->first_choice_str);
            $this->second_choice = strtotime($this->second_choice_str);
        }
        if ($this->state == self::APPROVED)
        {
            $this->VerifyValidDate('new_date_str');
            $this->VerifyNotEmpty('new_start_time');
            $this->VerifyNotEmpty('new_end_time');
            $this->VerifyTimeFormat('new_start_time', $this->new_start_time);
            $this->VerifyTimeFormat('new_end_time', $this->new_end_time);
            $this->new_date = strtotime($this->new_date_str);
        }

        if ($this->state == self::DENIED)
        {
            $this->VerifyNotEmpty('denial_reason');
        }

        $this->updated_at = time();

        return !$this->HasErrors();
    }

    public function AfterSave()
    {
        unset($this->_game);
    }

    public function SendEmail($subject_, $template)
    {
        $site_name = GetCfgVar('site_name');
        $subject = "[$site_name] $subject_";
        $schedulers = unserialize(GetCfgVar('resched:schedulers', $def));
        $ref_coordinators = unserialize(GetCfgVar('resched:ref_coordinators', $def));
        $cc_emails = unserialize(GetCfgVar('resched:cc_emails', $def));
        $to = array(); 
        $cc = array();
        foreach ($schedulers as $scheduler)
        {
            $to[] = "{$scheduler['name']} <{$scheduler['email']}>";
        }

        foreach ($ref_coordinators as $ref)
        {
            $cc[] = "{$ref['name']} <{$ref['email']}>";
        }

        foreach ($cc_emails as $cce)
        {
            $cc[] = "{$cce['name']} <{$cce['email']}>";
        }
        
        $cc[] = "{$this->requestor_name} <{$this->requestor_email}>";
        $cc[] = "{$this->opponent_name} <{$this->opponent_email}>";

        $data = array(
            'reschedule' => $this,
            'site_name' => $site_name,
            'host' => GetCfgVar('site_address'),
        );
         
        $email_data = array(
            'to' => implode(', ', $to),
            'cc' => implode(', ', $cc),
            'from' => "$site_name <" . GetCfgVar('site_email') . ">",
            'subject' => $subject,
            'type' => "html",
            'data' => $data
        );

        return \simp\Email::Send($template, $email_data);

    }

    public function SendRequestEmail()
    {
        $this->SendEmail("Match Reschedule Request", "reschedule_request");
    }

    public function SendAcceptEmail()
    {
        $this->SendEmail("Match Reschedule Approved", "reschedule_approved");
    }

    public function SendDenyEmail()
    {
        $this->SendEmail("Match Reschedule Denied", "reschedule_denied");
    }

    public function SendModifyEmail()
    {
        $this->SendEmail("Match Reschedule Modified", "reschedule_modified");
    }
}
