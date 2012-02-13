<?php
class Refunds extends \simp\Module
{

    protected static function OnInstall()
    {
        self::SetAdminInterface(true);
    }

    protected function Setup($args)
    {
        require_once "models/refund.php";
        require_once "models/refundable.php";
        require_once "models/refund_email.php";

        $this->SetPermissions(
            array(
                "index" => Ability::ADMIN,
                "add_email" => Ability::ADMIN,
                "edit_email" => Ability::ADMIN,
                "remove_email" => Ability::ADMIN,
                "add_refundable" => Ability::ADMIN,
                "edit_refundable" => Ability::ADMIN,
                "remove_refundable" => Ability::ADMIN,
                "remove_refund" => Ability::ADMIN,
                "remove_processed" => Ability::ADMIN,
                "show_refunds" => Ability::ADMIN,
                "download_refunds" => Ability::ADMIN,
            )
        );
        $this->refundables = \simp\Model::Find("Refundable", "expiry >= ?", array(time()));
    }

    //////// Admin actions
    public function Index()
    {
        $this->expired = \simp\Model::Find("Refundable", "expiry < ?", array(time()));
        $this->emails = \simp\Model::FindAll("RefundEmail");
        return true;
    }

    public function ShowRefunds($method, $params, $vars)
    {
        $this->pending = \simp\Model::Find("Refund", "processed = ?", array(false));
        $this->processed = \simp\Model::Find("Refund", "processed = ?", array(true));
        if ($method == \simp\Request::PUT)
        {
            //echo "<pre>" . print_r($vars, true) . "</pre>";
            $rows = array();
            $rows[] = array(
                "Player", "Requestor", "Program", "Payment Type", "Amount", "Reason", 
                "Submit Date", "Address 1", "Address 2", "City", "State", "Zip", "Email"
            );
            foreach ($vars['Request'] as $id => $val)
            {
                //echo "will process request id {$id}.   ";
                $request = \simp\Model::FindById("Refund", $id);
                $request->processed = true;
                $request->process_date = time();
                $request->Save();
                $rows[] = array(
                    $request->player, $request->requestor, $request->program->name, 
                    Refund::$payment_methods[$request->payment_method], $request->amount,
                    $request->reason, $request->submit_date_str, $request->address_1,
                    $request->address_2, $request->city, Refund::$states[$request->state],
                    $request->zip, $request->email
                );
            }

            $report_date = strftime("%m-%d-%Y_%H_%M_%S", time());
            $filename = "refunds.{$report_date}.csv";
            ob_start();
            header('Content-type: txt/plain');
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            foreach ($rows as $row)
            {
                echo implode(",", $row) . "\n";
            }
            $content = ob_get_contents();
            ob_end_clean();
            echo $content;
            return false;
        }
        return true;
    }

    public function AddEmail($method, $params, $vars)
    {
        $this->email = \simp\Model::Create("RefundEmail");
        if ($method == \simp\Request::POST)
        {
            $this->email->UpdateFromArray($vars['RefundEmail']);
            if ($this->email->Save())
            {
                AddFlash("Email {$this->email->email} added.");
                \Redirect(Path::module('refunds', 'admin'));
            } 
        }
        return true;
    }
    
    public function EditEmail($method, $params, $vars)
    {
        $this->email = \simp\Model::FindById("RefundEmail", $params['id']);
        if ($method == \simp\Request::PUT)
        {
            $this->email->UpdateFromArray($vars['RefundEmail']);
            if ($this->email->Save())
            {
                AddFlash("Email {$this->email->email} added.");
                \Redirect(Path::module('refunds', 'admin'));
            }
        }
        return true;
    }
    
    public function RemoveEmail($method, $params, $vars)
    {
        $email = \simp\Model::FindById("RefundEmail", $params['id']);
        if ($method == \simp\Request::DELETE)
        {
            $email->Delete();
        }
        \Redirect(Path::module('refunds', 'admin'));
    return true;}

    public function AddRefundable($method, $params, $vars)
    {
        $this->refundable = \simp\Model::Create("Refundable");
        if ($method == \simp\Request::POST)
        {
            $this->refundable->UpdateFromArray($vars['Refundable']);
            if ($this->refundable->Save())
            {
                AddFlash("Refundable program {$this->refundable->name} created.");
                \Redirect(Path::module('refunds', 'admin'));
            }
        }
        return true;
    }

    public function EditRefundable($method, $params, $vars)
    {
        $this->refundable = \simp\Model::FindById("Refundable", $params['id']);
        if ($method == \simp\Request::PUT)
        {
            $this->refundable->UpdateFromArray($vars['Refundable']);
            if ($this->refundable->Save())
            {
                AddFlash("Refundable program {$this->refundable->name} updated.");
                \Redirect(Path::module('refunds', 'admin'));
            }
        }
        return true;
    }

    public function RemoveRefundable($method, $params, $vars)
    {
        $refundable = \simp\Model::FindById("Refundable", $params['id']);
        if ($method == \simp\Request::DELETE && $refundable->id > 0)
        {
            $refundable->Delete();
        }
        \Redirect(Path::module('refunds', 'admin'));
    }

    public function RemoveRefund($method, $params, $vars)
    {
        $refund = \simp\Model::FindbyId("Refund", $params['id']);
        if ($method == \simp\Request::DELETE && $refund->id > 0)
        {
            $refund->Delete();
        }
        \Redirect(Path::module('refunds', 'admin', "show_refunds"));
    }

    public function RemoveProcessed($method, $params, $vars)
    {
        $refunds = \simp\Model::Find("Refund", "processed = ?", array(true));
        if ($method == \simp\Request::DELETE)
        {
            foreach ($refunds as $refund)
            {
                $refund->Delete();
            }
        }
        \Redirect(Path::module('refunds', 'admin', "show_refunds"));
    }

    public function DownloadRefunds($method, $params, $vars)
    {
        $refunds = \simp\Model::FindAll("Refund", "order by processed asc");
        $rows = array();
        $rows[] = array(
            "Processed", "Player", "Requestor", "Program", "Payment Type", "Amount", "Reason",
            "Submit Date", "Process Date", "Address 1", "Address 2", "City", "State", "Zip", "Email"
        );
        foreach ($refunds as $refund)
        {
            $processed = $refund->processed ? "Yes" : "No";
            $process_date = $refund->processed ? $refund->process_date_str : "";
            $rows[] = array(
                $processed, $refund->player, $refund->requestor, $refund->program->name,
                Refund::$payment_methods[$refund->payment_method], $refund->amount,
                $refund->reason, $refund->submit_date_str, $process_date, $refund->address_1,
                $refund->address_2, $refund->city, Refund::$states[$refund->state],
                $refund->zip, $refund->email
            );
        }
        $report_date = strftime("%m-%d-%Y_%H_%M_%S", time());
        $filename = "refunds.{$report_date}.csv";
        ob_start();
        header('Content-type: txt/plain');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        foreach ($rows as $row)
        {
            echo implode(",", $row) . "\n";
        }
        $content = ob_get_contents();
        ob_end_clean();
        echo $content;
        return false;
    }

    //////// User actions
    public function RefundablePrograms($method, $params, $vars)
    {
        $this->SetLayout('default');
        return true;
    }

    public function Request($method, $params, $vars)
    {
        $this->SetLayout('default');
        $this->refundable = \simp\Model::FindById("Refundable", $params['id']);
        $this->refund = \simp\Model::Create("Refund");
        $this->refund->refundable_id = $this->refundable->id;
        if ($method == \simp\Request::POST)
        {
            $this->refund->UpdateFromArray($vars['Refund']);
            $this->refund->processed = false;
            $this->refund->submit_date = time();
            if ($this->refund->Save())
            {
                $this->refund->SendEmail();
                AddFlash("Your request has been submitted.");
                \Redirect(Path::home());
            }
        }
            
        return true;
    }

}

