<?php
class Camp extends \simp\Model
{
    //TODO: add notice on for deadline (same for tournament)
    // fields
    //    name:
    //    short_name:
    //    start_date:
    //    start_time:
    //    end_time:
    //    deadline:
    //    end_date:
    //    location:
    //    registration_form:
    //    registration_link:
    //    assoc_league:
    //    description:
    //    fee:
    //    who:
    //
    public $file_info;
    public $start_date;
    public $end_date;
    public $start_time;
    public $end_time;
    public $reg_deadline;
    public $pdf_path;

    protected $path;
    protected $rel_path;
    protected $abs_path;

    public function Setup()
    {
        $this->file_info = NULL;
        global $REL_PATH;
        global $BASE_PATH;
        $path = "resources/files/pdf/";
        $this->rel_path = $REL_PATH . $path;
        $this->abs_path = $BASE_PATH . $path;
        $this->pdf_path = NULL;
        $this->SkipSanity("description");
    }

    public static function GetUpcoming()
    {
        $dt = new \DateTime("now");
        return \simp\Model::FIND(
            'Camp',
            'start >= ? order by start',
            array($dt->getTimestamp())
        );
    }

    public static function GetPast()
    {
        $dt = new \DateTime("now");
        return \simp\Model::Find(
            'Camp',
            'start < ? order by start',
            array($dt->getTimestamp())
        );
    }

    public function __set($property, $value)
    {
        switch ($property)
        {
        case "assoc_leagues":
            $this->associated_leagues = serialize($value);
            break;
        default:
            parent::__set($property, $value);
            break;
        }
    }

    public function __get($property)
    {
        switch($property)
        {
        case "leagues":
            $assoc_leagues = $this->assoc_leagues;
            $league_arr = array();
            foreach ($assoc_leagues as $league => $assoc)
            {
                if ($assoc == true)
                {
                    $league_arr[] = $league;
                }
            }
            return $league_arr;
            break;
        case "assoc_leagues":
            return unserialize($this->associated_leagues);
            break;
        case "file_path":
            return $this->rel_path . "tournament/{$this->short_name}";
            break;
        default:
            return parent::__get($property);
            break;
        }
    }

    public function OnLoad()
    {
        $this->start_date = FormatDateTime($this->start, "m/d/Y");
        $this->end_date = FormatDateTime($this->end, "m/d/Y");
        $this->start_time = FormatDateTime($this->stime, "g:i a");
        $this->end_time = FormatDateTime($this->etime, "g:i a");
        $this->reg_deadline = FormatDateTime($this->deadline, "m/d/Y");
        $this->pdf_path = $this->rel_path . "camp/{$this->short_name}/";
    }

    public function BeforeSave()
    {
        $errors = 0;

        if (strpos($this->registration_link, "http") == false &&
            strpos($this->registration_link, "/") != 1)
        {
            $this->registration_link = "http://" . $this->registration_link;
        }
        $arr = explode(" ", strtolower($this->name));
        $short_name = implode("_", $arr);
        $this->short_name = strlen($short_name) > 47 ?
            substr($short_name, 0, 47) :
            $short_name;

        if (!$this->VerifyDateFormat('start_date', $this->start_date))
        {
            $errors ++;
        }
        else
        {
            $dt = new \DateTime($this->start_date);
            $this->start = $dt->getTimestamp();
        }
        
        if (!$this->VerifyDateFormat('end_date', $this->end_date))
        {
            $errors++;
        }
        else
        {
            $dt = new \DateTime($this->end_date);
            $this->end = $dt->getTimestamp();
        }
        
        if (!$this->VerifyDateFormat('reg_deadline', $this->reg_deadline))
        {
            $errors++;
        }
        else
        {
            $dt = new \DateTime($this->reg_deadline);
            $this->deadline = $dt->getTimeStamp();
        }

        if (!$this->VerifyTimeFormat('start_time', $this->start_time))
        {
            $errors++;
        }
        else
        {
            $dt = new \DateTime($this->start_time);
            $this->stime = $dt->getTimeStamp();
        }

        if (!$this->VerifyTimeFormat('end_time', $this->end_time))
        {
            $errors++;
        }
        else
        {
            $dt = new \DateTime($this->end_time);
            $this->etime = $dt->getTimeStamp();
        }

        if ($this->end < $this->start)
        {
            $errors++;
            $this->SetError('end_date', "Camp end date must come after start date");
        }

        if ($this->deadline > $this->start)
        {
            $errors++;
            $this->SetError('app_deadline', "Registration deadline must come before start date");
        }

        if ($this->stime > $this->etime)
        {
            $errors++;
            $this->SetError('end_time', "End time must come after start time");
        }

        global $log;
        foreach ($this->file_info as $filetype => $info)
        {
            $log->logDebug("filetype $filetype info: " . print_r($info, true));
            if ($info['error'] != 0 && 
                $info['error'] != UPLOAD_ERR_NO_FILE &&
                $info['type'] != "application/pdf")
            {
                $errors++;
                $this->SetError($filetype, HumanCase($filetype) ." must be in PDF format.");
            }
            if ($info['error'] != UPLOAD_ERR_NO_FILE)
            {
                $this->$filetype = $info['name'];
                if ($this->CopyFile($info['tmp_name'], $info['name']) == false)
                {
                    $errors++;
                }
            }
        }
        return $errors == 0;
    }

    protected function CopyFile($file, $name)
    {
        $pdf_path = $this->abs_path . "camp/{$this->short_name}/";
        if (!is_dir($pdf_path))
        {
            $ok = mkdir($pdf_path, 0755, true);
            if ($ok == false)
            {
                $this->SetError($name, "Failed to create PDF path. Contact sysadmin.");
                return false;
            }
        }
        global $log;
        $log->logDebug("Moving $file to $pdf_path{$name}");
        if (move_uploaded_file($file, $pdf_path . $name) == false)
        {
            $this->SetError($name, "Failed to upload file: can't copy.");
            return false;
        }
        return true;
    }

}

