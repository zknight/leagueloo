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

    public function Setup()
    {
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
        $name = preg_replace("/\s/", "_", $this->name);
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
            $idx = $this->ComputeDivision() > 15 ? 1 : 0;
            return $this->gender_str[$this->gender][$idx];
            break;
        case "program_name":
            return \R::getCell(
                "select name from program where id = ?",
                array($this->program_id));
            break;
        default:
            return parent::__get($property);
            break;

        }

    }

}
