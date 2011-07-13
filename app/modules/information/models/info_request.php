<?php
class InfoRequest extends \simp\DummyModel
{ 

    public $fields;
    public $field_info;
    public function Setup()
    {
        $this->field_info = array();
        $this->fields = array();
    }

    public function AddFields($data)
    {
        foreach ($data as $datum)
        {
            //echo ("adding $data->label\n");
            $this->field_info[$datum->id] = array(
                'type' => $datum->type,
                'label' => $datum->label, 
                'required' => $datum->required,
                'opt' => $datum->options);
            print_r($this->field_info[$datum_id]['opt']);
        }
    }

    public function BeforeSave()
    {
        // iterate field_info first and check 'required' and 'type'
        foreach ($this->field_info as $did => $info)
        {
            if ($info['type'] == 1 && $info['required'])
            {
                $this->VerifyMinLength("fields[{$did}]", 3, "{$info['label']} is a required field.");
            }
        }

        if ($this->category->classification == Category::AGE)
        {
            $this->VerifyValidDate('birthdate');
        }

        // check body 
        $this->VerifyNotEmpty('body', "You haven't entered an enquiry message.");
        // check return address
        $this->VerifyEmail('email');
        $this->VerifyNotEmpty('name');

        //print_r($this->_errors);
        return !$this->HasErrors();
    }

    public function SendEmail()
    {
        // send email
        $subject = "You have an information request";
        $emails = array();
        global $log;
        $log->logDebug("In InfoRequest::SendEmail() classification = {$this->category->classification}");
        $message = "";
        if ($this->category->classification == Category::AGE)
        {
            //lookup user that matches age
            $bd = new \DateTime($this->birthdate);
            // calculate age in years from today
            $cur = new \DateTime('now');
            $month = $cur->format("n");
            $year = $cur->format("Y");
            $rel_date = "$year";
            if ($month < 8)
            {
                $rel_date = $year - 1;
            }
            $ds = "8/1/$rel_date";
            $start = new \DateTime($ds);

            $diff = $start->diff($bd);
            $age = $diff->format("%y");
            $log->logDebug("InfoRequest: player age: $age");
            \R::debug(true);
            $emails = \simp\Model::Find(
                "Email", 
                "category_id = ? and (param1 = ? or param1 = 0) and param2 <= ? and param3 >= ?",
                array($this->category->id, $this->gender, $age, $age));
            $log->logDebug("InfoRequest: emails " . print_r($emails, true));
            $message = "You have received a request from *{$this->name}* regarding:\n";
            $message .= "\t{$this->category->text}\n\n";
            $message .= "Email: {$this->email}\n";
            $y = $age-1;
            $g = $this->gender == 1 ? "male" : "female"; 
            $message .= "Player's Birthdate: {$this->birthdate} (U$y)\n";
            $message .= "Player's Gender: {$g}\n";
            
        }
        else if ($this->category->classification == Category::CUSTOM)
        {
            $emails[] = \simp\Model::FindById("Email", $this->email_id);
            $message = "You have received a request from *{$this->name}* regarding:\n";
            $message .= "\t{$this->category->text}\n\n";
            $message .= "Email: {$this->email}\n";
            $message .= "Subject: {$this->subject}\n";
        }
        else
        {
            $emails = \simp\Model::Find("Email", 'category_id = ?', array($this->category->id));
        }

        foreach ($this->field_info as $i => $info)
        {
            switch ($info['type'])
            {
            case 0:
                $val = $this->fields[$i] == 1 ? 'yes' : 'no';
                break;
            case 1:
                $val = $this->fields[$i];
                break;
            case 2:
                $d = \simp\Model::FindById('Datum', $i);
                $val = $d->options[$this->fields[$i]];
                break;
            }
            $message .= "{$info['label']}: {$val}\n";
        }

        $message .= "\nmessage:\n{$this->body}";

        $email_data = array(
            'type' => "text",
            'from' => "{$this->name} <{$this->email}>",
            'subject' => $subject,
            'data' => $message
        ); 
        foreach ($emails as $email)
        {
            $email_data['to'] = $email->address;
            \simp\Email::Send(false, $email_data);
        }
    }

}
