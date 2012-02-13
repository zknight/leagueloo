<?php
class Refund extends \simp\Model
{
    public static $payment_methods = array(
        "Check",
        "Cash",
        "Credit/Debit Card"
    );

    public static $states = array(
        "AK", "AL", "AR", "AZ", "CA", "CO", "CT", "DC", "DE", 
        "FL", "GA", "HI", "IA", "ID", "IL", "IN", "KS", "KY", 
        "LA",  "MA", "MD", "ME", "MI", "MN", "MO", "MS", "MT", 
        "NC", "ND", "NE",  "NH", "NJ", "NM", "NV", "NY", "OH", 
        "OK", "OR", "PA", "RI", "SC",  "SD", "TN", "TX", "UT", 
        "VA", "VT", "WA", "WI", "WV", "WY"); 

    public $process_date_str;

    public function OnLoad()
    {
        if ($this->id > 0)
        {
            $this->process_date_str = strftime("%m/%d/%Y", $this->process_date);
            $this->submit_date_str = strftime("%m/%d/%Y", $this->submit_date);
        }
    }

    public function __get($property)
    {
        if ($property == 'program')
        {
            $program = \simp\Model::FindById("Refundable", $this->refundable_id);
            return $program;
        }
        return parent::__get($property);
    }

    public function BeforeSave()
    {
        $this->VerifyEmail('email');
        $this->VerifyNotEmpty('requestor');
        $this->VerifyNotEmpty('player');
        $this->VerifyNotEmpty('amount');
        $this->VerifyNotEmpty('reason');
        $this->VerifyNotEmpty('address_1');
        $this->VerifyNotEmpty('city');
        $this->VerifyNotEmpty('zip');

        //$this->process_date = strtotime($this->process_date_str);
        //$this->submit_date = strtotime($this->submit_date_str);

        return ($this->HasErrors() == false);
    }

    public function SendEmail()
    {
        $site_name = GetCfgVar('site_name');
        $subject = "[REFUND REQUEST] {$this->player}";
        $emails = \simp\Model::FindAll("RefundEmail");
        $to = array();
        $cc = array();

        foreach ($emails as $email)
        {
            $salute = "{$email->name} <{$email->email}>";
            if ($email->cc_only)
            {
                $cc[] = $salute;
            }
            else
            {
                $to[] = $salute;
            }
        }

        $cc[] = "$this->requestor <{$this->email}>";

        $data = array(
            'refund' => $this,
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

        return \simp\Email::Send("refund_request", $email_data);
    }
}
