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
class Schedule extends \simp\DummyModel
{

    public static $column_map = array(
        'Game No.'          => 'gotsoccer_id',
        'Date'              => 'date',
        'Start'             => 'start_time',
        'End'               => 'end_time',
        'Age'               => 'age',
        'Sex'               => 'gender',
        'Division'          => 'division',
        'Field'             => 'field',
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
    public $matches;

    public function ImportFile($filename)
    {
        $row_data = array();
        $keys = array();
        $this->update_count = 0;
        $this->new_count = 0;
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
                    $match = \simp\Model::FindOrCreate(
                        "Match", 
                        "gotsoccer_id = ? and division = ? and age = ? and gender = ?",
                        array($row['gotsoccer_id'], $row['division'], $row['age'], $row['gender'])
                    );

                    $match->UpdateFromArray($row);
                    if ($match->id == 0) $this->new_count++;
                    else $this->update_count++;

                    if (!$match->Save())
                    {
                        // break out (maybe make this exception in the future?
                        return false;
                    }
                }
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

        $matches = \simp\Model::Find("Match", $conditions, $val);
        $by_date = array();
        foreach ($matches as $match) 
        {
            $div = "{$match->division} {$match->age} {$match->gender}";
            if (!array_key_exists($div, $by_date))
            {
                $by_date[$div] = array();
            }
            if (!array_key_exists($match->date, $by_date[$div]))
            {
                $by_date[$div][$match->date] = array();
            }
            $by_date[$div][$match->date][] = $match;
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

        // get all matches matching conditions
        $matches = \simp\Model::Find("Match", $conditions, $val);

        // generate array of times for each date

        // iterate matches by date and 'fill in' times that are between match dates

        $by_date = array();
        foreach ($matches as $match)
        {
            $date = $match->date;
            if (!array_key_exists($date, $by_date))
            {
                $by_date[$date] = array();
            }

        }

    }
}
