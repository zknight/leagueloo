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
    protected $_ability_level_strings;
    function Setup()
    {
        $this->_ability_level_strings = array(
            Ability::EDIT => 'edit',
            Ability::PUBLISH => 'publish',
            Ability::ADMIN => 'administer',
        );
    }

    function LevelString()
    {
        return $this->_ability_level_string[$this->level];
    }
}
