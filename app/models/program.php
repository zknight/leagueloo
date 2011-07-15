<?php
/// Program model
///
/// A Program is a main section of a Leagueloo site.
/// Examples are: Recreational Program, Competitive Program, Camp Program
/// Programs have associated news and events.
class Program extends \simp\Model
{

    const LEAGUE = 0;
    const TOURNAMENT = 1;
    const CAMP = 2;
    public static $types = array(
        Program::LEAGUE => 'league',
        Program::TOURNAMENT => 'tournament',
        Program::CAMP => 'camp',
    );

    public $file_info;
    protected $rel_path;
    protected $abs_path;
    public $img_path;

    // TODO: factor these out
    public $editors;
    public $publishers;
    public $admins;

    public function Setup()
    {
        $this->file_info = NULL;
        $this->img_path = NULL;
        global $REL_PATH;
        global $BASE_PATH;
        $path = "resources/files/img/";
        $this->rel_path = $REL_PATH . $path;
        $this->abs_path = $BASE_PATH . $path;
        $this->SkipSanity('description');
    }

    public function OnLoad()
    {
        $this->img_path = $this->rel_path . "Program/{$this->name}/{$this->image}";
    }

    public function BeforeSave()
    {
        $this->allow_teams = $this->type == 0;
        $errors = 0;

        if (!$this->VerifyNotEmpty('name')) $errors++;

        if ($errors == 0)
        {
            $img_path = $this->abs_path . "Program/{$this->name}/";
            $info = $this->file_info;
            $name = NULL;
            $err = ProcessImage(
                $info,
                $img_path,
                $name,
                array('max_height' => 315, 'max_width' => 420)
            );

            if ($name != "") $this->image = $name;

            if ($err != false)
            { 
                $errors++;
                $this->SetError("image", $err);
            }
        }

        return $errors == 0;
    }

    public static function CanAccessTournament($user, $level)
    {
        $tournament = self::FindOne("Program", "type = ?", array(\Program::TOURNAMENT));
        return $user->CanAccess("Program", $tournament->id, $level);
    }

    public static function CanAccessCamp($user, $level)
    {
        $camp = self::FindOne("Program", "type = ?", array(\Program::CAMP));
        return $user->CanAccess("Program", $camp->id, $level);
    }

    public static function FindByType($type)
    {
        return self::Find("Program", "type = ?", array($type));
    }

    public function Types()
    {
        if (Cache::Exists("program_types"))
            return Cache::Read("program_types");
        else
        {
            $types = array(Program::LEAGUE => 'league');
            if (self::Count("Program", "type = ?", array(Program::TOURNAMENT)) == 0)
            {
                $types[Program::TOURNAMENT] = 'tournament';
            }
            if (self::Count("Program", "type = ?", array(Program::CAMP)) == 0)
            {
                $types[Program::CAMP] = 'camp';
            }
            Cache::Write("program_types", $types);
            return $types;
        }
    }

    public function Type()
    {
        $types = Program::Types();
        return $types[$this->type];
    }

}
