<?
require_once "ability.php";
class User extends \simp\Model
{
    protected $_abilities;

    public function Setup()
    {
        global $log;
        $log->logDebug("in User::__construct()");
        $this->_abilities = array();
    }

    public function Abilities()
    {
        if (count($this->_abilities) == 0)
        {
            $this->_abilities = User::Find(
                "Ability",
                "user_id = ?",
                array($this->id));
        }

        return $this->_abilities;
    }

    public function AddAbility($ability)
    {
        //$ability->user_id = $this->id;
        //$retval = $ability->Save();
        $this->_abilities[] = $ability;
    }

    public function CanAccess($entity_type, $entity_id, $level)
    {
        if ($this->super) return true;
        $ability = User::FindOne(
            "Ability", 
            "user_id = ? and entity_type = ? and entity_id = ?",
            array($this->id, $entity_type, $entity_id));
        //echo "ability: " . print_r($ability, true);
        return $ability->level >= $level;
    }

    public function CanEdit($entity_type, $entity_id)
    {
        return $this->CanAccess($entity_type, $entity_id, Ability::EDIT);
    }

    public function CanPublish($entity_type, $entity_id)
    {
        return $this->CanAccess($entity_type, $entity_id, Ability::PUBLISH);
    }

    public function CanAdmin($entity_type, $entity_id)
    {
        return $this->CanAccess($entity_type, $entity_id, Ability::ADMIN);
    }

    public function __get($property)
    {
        if ($property == 'abilities')
        {
            return $this->Abilities();
        }
        else return parent::__get($property);
    }

    public function AfterSave()
    {
        echo "User::AfterSave()\n";
        foreach ($this->_abilities as $ability)
        {
            $ability->user_id = $this->id;
            $ability->Save();
        }
        return true;
    }

    public function BeforeDelete()
    {
        $this->Abilities();
        return true;
    }

    public function AfterDelete()
    {
        foreach ($this->_abilities as $ability)
        {
            $ability->Delete();
        }
        return true;
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
