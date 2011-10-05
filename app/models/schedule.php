<?php
/*
fields of importance
gotsoccer_id: Game No.
date: Date
start_time: Start
end_time: End
age: Age
gender: Sex
division: Division
field: Field
home: Home Team
home_club: Home Club
home_score: Home Final Score
away: Away Team
away_club: Away Club
away_score: Away Final Score
home_full_name: Home Team Full
away_fill_name: Away Team Full

by field
by division
    by age
    by gender
*/
class Schedule extends \simp\Model
{


    public static $column_map = array(
        'Game No.'          => 'gotsoccer_id',
        'Date'              => 'date_str',
        'Start'             => 'start_time',
        'End'               => 'end_time',
        'Age'               => 'age',
        'Sex'               => 'gender',
        'Division'          => 'division',
        'Field'             => 'field_name',
        'Home Team'         => 'home',
        'Home Club'         => 'home_club',
        'Home Final Score'  => 'home_score',
        'Home Team Full'    => 'home_full_name',
        'Away Team'         => 'away',
        'Away Club'         => 'away_club',
        'Away Final Score'  => 'away_score',
        'Away Team Full'    => 'away_full_name',
    );

    public $new_count;
    public $update_count;
    public $start_date_str;
    public $end_date_str;
    protected $_divisions;
    protected $_formats;

    public static $ages = array(
        "U5", "U6", "U7", "U8", 
        "U9", "U10", "U11", "U12", 
        "U13", "U14", "U15", "U16",
        "U17", "U18", "U19");

    public function Setup()
    {
        $_divisions = array();
        $_formats = array();
    }

    public function OnLoad()
    {
        $this->start_date_str = FormatDateTime($this->start_date, "m/d/Y");
        $this->end_date_str = FormatDateTime($this->end_date, "m/d/Y");
    }

    public function __get($property)
    {
        switch ($property)
        {
        case "divisions":
            if (empty($this->_games))
            {
                $this->_divisions = \simp\Model::Find("Division", "schedule_id = ? order by name asc", array($this->id));
            }
            return $this->_divisions;
            break;
        case "format":
            if (empty($this->_formats))
            {
                $this->_formats = unserialize($this->formats);
            }
            return $this->_formats;
            break;
        default:
            return parent::__get($property);
        }
    }

    public function __set($property, $value)
    {
        switch($property)
        {
        case "format":
            $this->formats = serialize($value);
            break;
        default:
            parent::__set($property, $value);
            break;
        }
    }

    public function BeforeSave()
    {
        $this->VerifyNotEmpty("start_date_str");
        $this->VerifyNotEmpty("end_date_str");
        $this->VerifyNotEmpty("name");
        $this->VerifyDateFormat("start_date_str", $this->start_date_str);
        $this->VerifyDateFormat("end_date_str", $this->end_date_str);
        $this->start_date = strtotime($this->start_date_str);
        $this->end_date = strtotime($this->end_date_str);

        return !$this->HasErrors();
    }

    public function BeforeDelete()
    {
        // make sure divisions are removed...
        foreach ($this->divisions as $division)
        {
            $division->Delete();
        }

        return true;
    }

    public function ImportFile($filename)
    {
        $row_data = array();
        $keys = array();
        $this->update_count = 0;
        $this->new_count = 0;
        $cur_time = time();
        $f = \simp\Model::FindAll("Field");
        $d = \simp\Model::Find("Division", "schedule_id = ?", array($this->id));
        $fields = array();
        $divisions = array();
        foreach ($f as $field)
        {
            $fields[$field->gotsoccer_name] = $field;
        }
        foreach ($d as $division)
        {
            $divisions[$division->name] = $division;
        }

        if (($handle = fopen($filename, "r")) != FALSE)
        {
            // first line is columns
            if (($keys = fgetcsv($handle, 1024)) != FALSE)
            {
                while (($row_data = fgetcsv($handle, 1024)) != FALSE)
                {
                    echo ".";
                    $row = array();
                    foreach ($keys as $key)
                    {
                        $col = self::$column_map[$key];
                        $val = array_shift($row_data);
                        if (isset($col))
                        {
                            $row[$col] = $val;
                        }
                    }
                    $division_name = "{$row['age']} {$row['gender']} - {$row['division']}";
                    $game = \simp\Model::FindOrCreate(
                        "Game", 
                        "gotsoccer_id = ? and division = ? and gender = ? and age = ?",
                        array($row['gotsoccer_id'], $row['division'], $row['gender'], $row['age'])
                    );

                    // see if there is a division that game this one
                    if (array_key_exists($division_name, $divisions))
                    {
                        $d = $divisions[$division_name];
                        $game->division_id = $d->id;
                    }
                    else
                    {
                        $d = \simp\Model::Create("Division");
                        $d->name = $division_name;
                        $d->level = $this->level;
                        $d->schedule_id = $this->id;
                        $d->format = $this->format[$row['age']];
                        $d->Save();
                        $game->division_id = $d->id;
                        $divisions[$d->name] = $d;
                    }

                    // see if there is a field that game this one
                    if (array_key_exists($row['field_name'], $fields))
                    {
                        $f = $fields[$row['field_name']];
                        $game->field_id = $f->id;
                        $f->AddDivision($divisions[$division_name]);
                        //$fields[$row['field']->AddDivision($game->division, $game->age, $game->gender);
                    }
                    else
                    {
                        // create a field, default first complex
                        $complex = \simp\Model::FindOne("Complex", "1", array());
                        $f = \simp\Model::Create("Field");
                        $f->gotsoccer_name = $row['field_name'];
                        $f->complex_id = $complex->id;
                        $f->name = $row['field_name'];
                        $f->AddDivision($divisions[$division_name]);
                        $f->Save();
                        $fields[$f->gotsoccer_name] = $f;
                    }

                    $game->UpdateFromArray($row);
                    $game->updated_at = $cur_time;
                    //$game->schedule_id = $this->id;
                    $game->in_gotsoccer = true;
                    if ($game->id == 0) $this->new_count++;
                    else $this->update_count++;

                    if (!$game->Save())
                    {
                        // break out (maybe make this exception in the future?
                        global $log;
                        $log->logError("models/schedule: " . print_r($game->GetErrors(), true));
                        return false;
                    }

                }
            }
            foreach ($fields as $f)
            {
                $f->Save();
            }
        }
        return true;
    }

    public static function GetScheduleByDate($division=null, $age=null, $gender=null)
    {
        $all = true;
        $cond = array();
        $val = array();
        if (isset($division)) 
        {
            $cond[] = "division like ?";
            $val[] = $division;
            $all = false;
        }
        if (isset($age))
        {
            $cond[] = "age = ?";
            $val[] = $age;
            $all = false;
        }   
        if (isset($gender))
        {
            $cond[] = "gender = ?";
            $val[] = $gender;
            $all = false;
        }
        if ($all)
        {
            $conditions = "1";
        }
        else
        {
            $conditions = implode(" and ", $cond);
        }
        $conditions .= " order by date, start_time";

        $games = \simp\Model::Find("Game", $conditions, $val);
        $by_date = array();
        foreach ($games as $game) 
        {
            $div = "{$game->division} {$game->age} {$game->gender}";
            if (!array_key_exists($div, $by_date))
            {
                $by_date[$div] = array();
            }
            if (!array_key_exists($game->date_str, $by_date[$div]))
            {
                $by_date[$div][$game->date_str] = array();
            }
            $by_date[$div][$game->date_str][] = $game;
        }
        return $by_date;
    }

    public function GetFieldUsageByDate($field=null, $date=null)
    {
        $all = true;
        $cond = array();
        $val = array();
        if (isset($field))
        {
            $cond[] = "field like ?";
            $val[] = $field;
            $all = false;
        }
        if (isset($date))
        {
            $cond[] = "date = ?";
            $val[] = $date;
            $all = false;
        }

        if ($all)
        { 
            $conditions = "1";
        }
        else
        {
            $conditions = implode(" and ", $cond);
        }
        $conditions .= " order by date, field, start_time";

        // get all games matching conditions
        $games = \simp\Model::Find("game", $conditions, $val);

        // generate array of times for each date
        $times = $this->GenerateTimeSlots("7:00", "21:00", 30);

        // iterate games by date and 'fill in' times that are between game dates

        $by_date = array();
        foreach ($games as $game)
        {
            $date = $game->date_str;
            $field = $game->field;
            if (!array_key_exists($date, $by_date))
            {
                $by_date[$date] = array();
            }
            if (!array_key_exists($field, $by_date[$date]))
            {
                $by_date[$date][$field] = array();
                foreach ($times as $time)
                {
                    $by_date[$date][$field][$time] = false;
                }
            }
            $st = strtotime($game->start_time);
            $et = strtotime($game->end_time);
            for ($t = $st; $t < $et; $t += 1800)
            {
                $by_date[$date][$field][$t] = true;
            }
        }

        return array($times, $by_date);
    }

    // increment is in minutes
    protected function GenerateTimeSlots($start, $end, $increment)
    {
        $sh = 0; $sm = 0;
        $eh = 0; $em = 0;
        list($sh, $sm) = explode(":", $start);
        list($eh, $em) = explode(":", $end);
        // convert to integers (minutes)
        $stime = $sh * 60 + $sm;
        $etime = $eh * 60 + $em;
        $times = array();

        for ($t = $stime; $t <= $etime; $t += $increment)
        {
            $th = floor($t / 60);
            $tm = $t % 60;
            $tstr = sprintf("%d:%02d", $th, $tm);
            $times[] = strtotime($tstr);
        }

        return $times;
    }
}
