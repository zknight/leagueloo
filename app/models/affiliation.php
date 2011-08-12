<?php
class Affiliation extends \simp\Model
{
    const MANAGER = 1;
    const TREASURER = 2;
    const MEMBER = 3;

    public $team_name;

    public static $types = array(
        self::MANAGER => "Team Manager",
        self::TREASURER => "Team Treasurer",
        self::MEMBER => "Team Member"
    );

    public function Setup()
    {
        $this->team_name = "";
    }

    public function OnLoad()
    {
        if ($this->id > 0)
        {
            $team = \simp\Model::FindById("Team", $this->team_id);
            $this->team_name = $team->name; //"{$team->division} {$team->short_name} {$team->gender_str}";
        }
        else
        {
            $this->confirmed = false;
        }
    }

    public function BeforeSave()
    {
        $team = \simp\Model::FindById("Team", $this->team_id);
        $this->team_name = "{$team->division} {$team->name} {$team->gender_str}";
        return true;
    }

    public function AfterFirstSave()
    {
        // Depending upon type, send email to approriate person.  If no one is in charge,
        // send it to site administrator
        $users = array();

        switch ($this->type)
        {
        case self::MANAGER:
            // send to site admin?
            $users = $this->GetAdmins();
            break;
        case self::TREASURER:
        case self::MEMBER:
            $q = "select user.* from user, ability ";
            $q .= "where user.id = ability.user.id and ability.entity_type = ? and ability.entity_id = ? ";
            $q .= "and ability.level = ?";
            $result = \R::getAll($q, array("team", $this->team_id, Ability::ADMIN));
            $beans = \R::convertToBeans("User", $result);
            foreach ($beans as $bean)
            {
                $users[] = \simp\Model::FromBean('User', $bean);
            }
            $admins = $this->GetAdmins();
            $users = array_merge($users, $admins);
            break;
        }

        $site_name = GetCfgVar('site_name');
        $subject = "[{$site_name}] Team Affiliation Request";
        foreach ($users as $user)
        {
            $user->Notify($subject, 'affiliation', array('affiliation' => $this, 'requestor' => CurrentUser()));
        }
    }

    protected function GetAdmins()
    {
        $users = array();
        $program_name = \R::getCell("select program.name from team, program where team.id = ? and program.id = team.program_id", array($this->team_id));
        $q = "select user.* from user, ability, program, team ";
        $q .= "where user.id = ability.user_id and ability.entity_type = ? and ability.entity_id = program.id ";
        $q .= "and ability.level = ? and program.name = ?";
        $result = \R::getAll($q, array("program", Ability::ADMIN, $program_name));
        $beans = \R::convertToBeans("user", $result);
        foreach ($beans as $bean)
        {
            $users[] = \simp\Model::FromBean('User', $bean);
        }
        $supers = \simp\Model::Find('User', 'super = ?', array(true));
        $users = array_merge($users, $supers);
        global $log; $log->logDebug("Affiliation: " . print_r($users, true));
        return $users;
    }

    public static function GetType($user_id, $team_id)
    {
        $aff = \simp\Model::FindOne('Affiliation', 'user_id = ? and team_id = ?', array($user_id, $team_id));
        if (isset($aff))
        {
            return $aff->type;
        }
        return 0;
    }

    public static function GetUsers($team_id)
    {
        $users = array();
        $q = "select user.* from user, affiliation ";
        $q .= "where user.id = affiliation.user_id and affiliation.team_id = ?";

        $result = \R::getAll($q, array($team_id));
        return $result;
    }

}
