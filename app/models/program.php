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

    public function Setup()
    {
    }

    public function BeforeSave()
    {
        $this->allow_teams = $this->type == 0;
        return true;
    }

    public function Type()
    {
        return Program::$types[$this->type];
    }


}
