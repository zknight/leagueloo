<?
require_once "ability.php";
class User extends \simp\Model
{
    protected $_abilities;
    protected $_password;
    protected $_password_verify;

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

    public function UpdateAbilities($ability_array)
    {
        foreach($ability_array as $vals)
        {
            if (isset($vals['id']))
            {
                $ability = User::FindById('Ability', $vals['id']);
                global $log;
                $log->logDebug("UpdateAbilities: ability[{$vals['id']}]: \n" . print_r($ability, true));
                if ($vals['level'] > 0)
                {
                    $ability->UpdateFromArray($vals);
                    $this->AddAbility($ability);
                }
                else
                {
                    $ability->Delete();
                }
            }
            else
            {
                if ($vals['level'] > 0)
                {
                    $ability = User::Create('Ability');
                    $ability->UpdateFromArray($vals);
                    $this->AddAbility($ability);
                }
            }
        }

    }

    public function AddAbility($ability)
    {
        //$ability->user_id = $this->id;
        //$retval = $ability->Save();
        if (is_a($ability, 'Ability'))
        {
            $this->_abilities[] = $ability;
        }
    }

    public function Authenticate($password)
    {
        $ok = true;
        $phash = sha1($password . $this->pass_salt);
        global $log;
        $log->logDebug("phash: {$phash} pass_hash: {$this->pass_hash}");
        if ($this->pass_hash != $phash)
        {
            $this->_errors['password'] = "Invalid password";
            $ok = false;
        }
        return $ok;
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
    
    public function __set($property, $value)
    {
        global $log;
        $log->logDebug("User: setting $property to $value");
        if ($property == "password")
        {
            $this->_password = $value;
        }
        else if ($property == "password_verification")
        {
            $this->_password_verify = $value;
        }
        else if ($property == "unverified")
        {
            parent::__set("verified", !$value);
        }
        else parent::__set($property, $value);
    }

    public function BeforeSave()
    {

        $errors = 0;
        if ($this->id == 0 && $this->_password == "")
        {
            $this->_errors['password'] = "Password required.";
            $errors++;
        }
        if ($this->_password == $this->_password_verify)
        {
            if ($this->_password != "")
            {
                $psalt = RandStr(40);
                $phash = sha1($this->_password . $psalt);
                $this->pass_hash = $phash;
                $this->pass_salt = $psalt;
            }
            $pass = true;
        }
        else 
        {
            $this->_errors['password_verification'] = "Password and confirmation don't match.";
            $errors++;
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL))
        {
            $this->_errors['email'] = "Please enter a valid email address.";
            $errors++;
        }

        if ($this->login == "")
        {
            $this->_errors['login'] = "'Login' is a required field.";
            $errors++;
        }
        else if ($this->id == 0)
        {
            $user_with_matching_login = User::FindOne("User", "login=?", array($this->login));
            if (isset($user_with_matching_login))
            {
                $this->_errors['login'] = "That login already exists.  Please try another.";
                $errors++;
            }
        }

        if (!$this->verified)
        {
            $this->verification_string = sha1($this->login . RandStr(40));
        }

        return $errors == 0;
    }

    public function AfterSave()
    {
        foreach ($this->_abilities as $ability)
        {
            $ability->user_id = $this->id;
            $ability->Save();
        }

        if (!$this->verified)
        {
            // send email with verification string
            $from = GetCfgVar('site_email');
            $to = $this->email;
            $subject = "Please confirm your account.";
            $message = "You have recently signed up for an account at the ";
            $message .= GetCfgVar("site_name", "Leagueloo");
            $message .= "website.  Please confirm this by following this link:\n";
            $message .= "(TODO change) http://localhost/web/leagueloo/user/confirm/";
            $message .= $this->verification_string;
            $headers = "From: " . $from . "\r\n" .
                "Reply-To: " . $from . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            mail($to, $subject, $message, $headers);
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
