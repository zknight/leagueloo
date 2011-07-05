<?php
// TODO: add more validation
class Tournament extends \simp\Model
{
    public static $age_divisions = array(
        6 => 'Under 6',
        7 => 'Under 7',
        8 => 'Under 8',
        9 => 'Under 9',
        10 => 'Under 10',
        11 => 'Under 11',
        12 => 'Under 12',
        13 => 'Under 13',
        14 => 'Under 14',
        15 => 'Under 15',
        16 => 'Under 16',
        17 => 'Under 17',
        18 => 'Under 18',
        19 => 'Under 19',
    );

    public static $formats = array(
        11 => '11 v 11',
        8 => '8 v 8',
        6 => '6 v 6',
        4 => '4 v 4',
        3 => '3 v 3',
    );

    public static $match_length = array(
        45 => '45 min halves',
        40 => '40 min halves',
        35 => '35 min halves',
        30 => '30 min halves',
        25 => '25 min halves',
        20 => '20 min halves',
        8 => '8 min quarters',
    );

    public $file_info;
    public $start_date;
    public $end_date;
    public $app_deadline;
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
        return \simp\Model::Find(
            'Tournament',
            'start >= ? order by start',
            array($dt->getTimestamp())
        );
    }

    public static function GetPast()
    {
        $dt = new \DateTime("now");
        return \simp\Model::Find(
            'Tournament',
            'start < ? order by start',
            array($dt->getTimestamp())
        );
    }

    public function __set($property, $value)
    {
        switch($property)
        {
        case "assoc_leagues":
            $this->associated_leagues = serialize($value);
            break;
        case "age_division":
            $this->div_present = serialize($value);
            break;
        case "format":
            $this->match_formats = serialize($value);
            break;
        case "match_length":
            $this->match_lengths = serialize($value);
            break;
        case "max_roster":
            $this->max_rosters = serialize($value);
            break;
        case "max_guest":
            $this->max_guests = serialize($value);
            break;
        case "cost":
        case "team_fee":
            $this->team_fees = serialize($value);
            break;
        default:
            parent::__set($property, $value);
            break;
        }
    }

    public function __get($property)
    {
        switch ($property)
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
        case "age_division":
            return unserialize($this->div_present);
            break;
        case "format":
            return unserialize($this->match_formats);
            break;
        case "match_length":
            return unserialize($this->match_lengths);
            break;
        case "max_roster":
            return unserialize($this->max_rosters);
            break;
        case "max_guest":
            return unserialize($this->max_guests);
            break;
        case "cost":
        case "team_fee":
            return unserialize($this->team_fees);
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
        $this->app_deadline = FormatDateTime($this->deadline, "m/d/Y");
        $this->pdf_path = $this->rel_path . "tournament/{$this->short_name}/";
    }
    /*
    protected function SetDivInfo($in_arr, $field)
    {
        foreach ($in_arr as $div => $val)
        {
            if (!array_key_exists($div, $this->div_info))
            {
                $this->div_info[$div] = array();
            }
            $this->div_info[$div][$field] = $val;
        }
    }
    */

    public function BeforeSave()
    {
        $errors = 0;

        // short name
        $arr = explode(" ", strtolower($this->name));
        $short_name = implode("_", $arr);
        $this->short_name = strlen($short_name) > 47 ?
            substr($short_name, 0, 47) :
            $short_name;

        // fix up dates
        if (!$this->VerifyDateFormat('start_date', $this->start_date))
        {
            $errors++;
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
        
        if (!$this->VerifyDateFormat('app_deadline', $this->app_deadline))
        {
            $errors++;
        }
        else
        {
            $dt = new \DateTime($this->app_deadline);
            $this->deadline = $dt->getTimestamp();
        }

        if ($this->end < $this->start)
        {
            $errors++;
            $this->SetError('end_date', "Tournament end date must come after start date");
        }

        if ($this->deadline > $this->start)
        {
            $errors++;
            $this->SetError('app_deadline', "Application deadline must come before start date");
        }
        
        // look at file attachments
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
        // don't save for now
        //return false;
    }

    protected function CopyFile($file, $name)
    {
        $pdf_path = $this->abs_path . "tournament/{$this->short_name}/";
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
