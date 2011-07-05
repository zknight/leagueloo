<?
class Ability extends \simp\Model
{
    const EDIT = 1;
    const PUBLISH = 2;
    const ADMIN = 3;
    // access type: program, app, team
    // access entity name: name of program, app, or team
    // access entity id: id of entity
    // access level: admin, publish, edit
    // user id
    public static $ability_level_strings =  array(
        Ability::EDIT => 'editor',
        Ability::PUBLISH => 'publisher',
        Ability::ADMIN => 'administrator',
    );

    function Setup()
    {
    }

    function LevelString()
    {
        return self::$ability_level_strings[$this->level];
    }
}
