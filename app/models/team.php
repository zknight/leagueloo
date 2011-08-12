<?
/// Team model
///
/// data fields
///     name
///     gender
///     year 
///     program_id
///     program_name (may be pseudo-field)
/// derived
///     division (u10, u11, etc)
class Team extends \simp\Model
{

    protected $gender_str;
    //public $coaches;
    public $file_info;
    public $rel_path;
    public $abs_path;
    public $img_path;
    public $gotsoccer;
    public $name;

    public function Setup()
    {
        //$this->coaches = array();
        $this->img_path = NULL;
        global $REL_PATH;
        global $BASE_PATH;
        $path = "resources/files/img/";
        $this->rel_path = $REL_PATH . $path;
        $this->abs_path = $BASE_PATH . $path;
        $this->gender_str = array(
            "m" => array("boys", "men"),
            "f" => array("girls", "women")
        );
    }

    public static function GetCurrentYearStart()
    {
        $dt = new \DateTime("now");
        $month = $dt->format("n");
        $year = $dt->format("Y");
        $rel_date = "$year";
        if ($month < 8)
        {
            $rel_date = $year - 1;
        }
        $ds = "8/1/$rel_date";
        global $log; $log->logDebug("GetCurrentYearStart(): $ds");
        $foo = strtotime($ds);
        $log->logDebug(FormatDateTime($foo, "F jS, Y"));
        return $foo;
    }

    public static function GetCurrentYearEnd()
    {
        $dt = new \DateTime("now");
        $month = $dt->format("n");
        $year = $dt->format("Y");
        $rel_date = "$year";
        if ($month > 7)
        {
            $rel_date = $year + 1;
        }
        return strtotime("7/31/$rel_date");
    }

    public static function GetYearFromDivision($division)
    {
        $division = preg_replace('/[uU]/', '', $division);
        $dt = new \DateTime();
        $dt->setTimestamp(self::GetCurrentYearStart());
        $year = $dt->format("Y") - $division + 1;
        return $year;
    }

    public function GetDivisions()
    {
        $divisions = array();
        $dt = new \DateTime();
        $dt->setTimestamp(self::GetCurrentYearStart());
        $year = $dt->format("Y") - 4;
        for ($y = 5; $y < 20; $y++)
        {
            $divisions[$year] = "U$y";
            $year --;
        }
        return $divisions; 
    }

    public function GetGenders()
    {
        return array("m" => "Boys/Men", "f" => "Girls/Women");
    }

    protected function ComputeDivision()
    {
        $dt = new \DateTime();
        $dt->setTimestamp(self::GetCurrentYearStart());
        $year = $dt->format("Y") - $this->year;
        global $log; $log->logDebug("ComputeDivision() " . $year + 1);
        return $year + 1;
    }

    public function NameForLink()
    {
        $name = preg_replace("/\s/", "_", $this->short_name);
        return $name;
    }

    public function __get($property)
    {
        switch ($property)
        {
        case "division":
            return ("U" . $this->ComputeDivision());
            break;
        case "gender_str":
            $idx = $this->ComputeDivision() > 18 ? 1 : 0;
            return $this->gender_str[$this->gender][$idx];
            break;
        case "program_name":
            return \R::getCell(
                "select name from program where id = ?",
                array($this->program_id));
            break;
        case "program_designator":
            return "{$this->program_type}:{$this->program_id}";
            break;
        case "coaches":
            $coach_beans = \R::related($this->_bean, 'coach');
            $coaches = array();
            foreach ($coach_beans as $id => $bean)
            {
                $coaches[$id] = new Coach($bean);
            }
            return $coaches;
            break;
        default:
            return parent::__get($property);
            break;

        }

    }

    public function __set($property, $value)
    {
        switch($property)
        {
        case "program_designator":
            list($this->program_type, $this->program_id) = explode(":", $value);
            break;
        default:
            parent::__set($property, $value);
            break;
        }
    }

    public function OnLoad()
    {
        $this->img_path = $this->rel_path . "team_pics/{$this->image}";
        if ($this->id > 0)
        {
            $link = self::FindOne(
                'Link', 
                'entity_type = ? and entity_id = ? and text = ?',
                array('Team', $this->id, 'Gotsoccer Ranking'));
            if ($link)
                $this->gotsoccer = $link->uri;
            $this->name = "{$this->division} {$this->short_name} {$this->gender_str[$this->gender][0]}";
        }
    }


    public function BeforeSave()
    {
        $errors = 0;


        $this->name = "{$this->division} {$this->short_name} {$this->gender_str[$this->gender][0]}";
        // check for existing on first save
        if ($this->id < 1)
        {
            $count = \simp\Model::Count(
                'Team',
                'short_name = ? and year = ? and gender = ?',
                array($this->short_name, $this->year, $this->gender)
            );
            
            if ($count > 0)
            {
                $errors++;
                $this->SetError('short_name', "This team already exists.");
            }
        }

        $errors = $this->VerifyNotEmpty('short_name') ? $errors : $errors+1;

        if ($errors == 0 && isset($this->file_info))
        {
            $img_path = $this->abs_path . "team_pics/";
            $info = $this->file_info;
            $img_name = NULL;
            $err = ProcessImage(
                $info,
                $img_path,
                $img_name,
                array('max_width' => 400)
            );

            if ($img_name != "") $this->image = $img_name;

            if ($err != false)
            {
                $errors++;
                $this->SetError("image", $err);
            }
        }


        return $errors == 0;
    }

    public function AfterSave()
    {
        $link = self::FindOrCreate(
            'Link', 
            'entity_type = ? and entity_id = ? and text = ?',
            array('team', $this->id, 'Gotsoccer Ranking'));

        if ($link->id == 0)
        {
            $link->entity_type = 'team';
            $link->text = "Gotsoccer Ranking";
            $link->entity_id = $this->id;
            $link->disabled = false;
            $link->new_window = true;
            global $log;
            $log->logDebug("Team::AfterSave link = " . print_r($link, true));
        }
        $link->uri = $this->gotsoccer;
        ob_start();
        //\R::debug(true);
        $link->Save();
        //\R::debug(false);
        //$log->logDebug("Team::AfterSave: " . ob_get_contents());
        //ob_end_clean();
        //$log->logDebug("Team::AfterSave link (after save) = " . print_r($link, true));
    }

    public function BeforeDelete()
    {
        // unassociated coach(es)
        foreach ($this->coaches as $coach)
        {
            \R::unassociate($this->_bean, $coach->Bean());
        }
        return true;
    }

}
