<?
require_once "ability.php";
class User extends \simp\Model
{
    public $abilities;

    public function Setup()
    {
        global $log;
        $log->logDebug("in User::__construct()");
        $this->AddComposite("Ability", true);
        $this->abilities = array();
    }

    public function Abilities()
    {
        return $this->abilities;
    }

    // login
    // first_name
    // last_name
    // salt
    // hash
    // last_login
    // created_on
    // abilities (through user_ability)

    // TODO functions for: 
    // get_abilities()
    // add_ability(type, level, id)
    // has_access(type, level, id)
}
