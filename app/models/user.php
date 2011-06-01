<?
require_once "ability.php";
class User extends \simp\Model
{
    protected $_abilities;
    protected $_password;
    protected $_password_verify;

    const EMAIL = 0;
    const MESSAGE = 1;
    const EMAIL_AND_MESSAGE = 2;
    const NONE = 3;

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

    public function Verify($token)
    {
        global $log;
        $log->logDebug("comparing $token to {$this->verification_string}");
        if ($token == $this->verification_string)
        {
            $this->verified = true;
        }
        return $this->verified;
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
        global $log;
        $entity_type = SnakeCase($entity_type);
        $log->logDebug("CanAccess: checking $entity_type, $entity_id, $level");
        if ($this->super) return true;
        $ability = User::FindOne(
            "Ability", 
            "user_id = ? and entity_type = ? and entity_id = ?",
            array($this->id, $entity_type, $entity_id));
        //echo "ability: " . print_r($ability, true);
        if ($ability)
        {
            $log->logDebug("CanAccess: found {$ability->level}");
            return $ability->level >= $level;
        }
        return false;
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

    public function FindPublishers($entity_type, $entity_id)
    {
        $publishers = array();
        $q  = "select user.* from user, ability ";
        $q .= "where user.id = ability.user_id and ability.entity_type = ? and ability.entity_id = ?";
        $q .= " and ability.level >= ?";
        
        $result = \R::getAll(
            $q,
            array(SnakeCase($entity_type), $entity_id, Ability::PUBLISH)
        );

        if (isset($result))
        {
            foreach($result as $row)
            {
                $bean = \R::dispense('user');
                $bean->import($row);
                $publishers[] = new User($bean);
            }
        }

        /*
        $publishers = User::Find(
            "Ability",
            "entity_type = ? and entity_id = ? and level >= ?",
            array($entity_type, $entity_id, Ability::PUBLISH));
         */
        $super_users = User::Find(
            "User",
            "super = ?",
            array(true));

        return array_merge($publishers, $super_users);

    }

    public function __get($property)
    {
        switch ($property)
        {
        case "abilities":
            return $this->Abilities();
            break;
        case "password":
            return $this->_password;
            break;
        case "messages":
            $messages = User::Find("Message", "user_id = ?", array($this->id));
            return $messages;
        case "unread_message_count":
            //\R::debug(true);
            $count = \R::getCell("select count(*) from message where user_id = ? and unread = ?",
                array($this->id, true));
            //global $log; $log->logDebug("User::__get() umc: " . print_r($count, true));
            //\R::debug(false);
            return $count;
            break;
        default: 
            return parent::__get($property);
            break;
        }
    }
    
    public function __set($property, $value)
    {
        global $log;
        $log->logDebug("User: setting $property to $value");
        switch ($property)
        {
        case "password":
            $this->_password = $value;
            break;
        case "password_verification":
            $this->_password_verify = $value;
            break;
        case "unverified":
            parent::__set("verified", !$value);
            break;
        default:
            parent::__set($property, $value);
            break;
        }
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

    // News stuff

    public function GetUnpublishedNews()
    {
        $news = array();
        if ($this->super)
        {
            $news = News::FindUnpublished();
        }
        else
        {
            $abilities = User::Find(
                "Ability",
                "user_id = ? and level >= ?",
                array($this->id, Ability::EDIT));

            foreach ($abilities as $ability)
            {
                $news = array_merge(
                    $news,
                    News::FindUnpublished($ability->entity_type, $ability->entity_id));
            }
        }
        return $news;
    }

    public function GetPublishedNews()
    {
        $news = array();
        if ($this->super)
        {
            $news = News::FindPublished();
        }
        else
        {
            $abilities = User::Find(
                "Ability",
                "user_id = ? and level > ?",
                array($this->id, Ability::EDIT));

            foreach ($abilities as $ability)
            {
                $news = array_merge(
                    $news,
                    News::FindPublished($ability->entity_type, $ability->entity_id));
            }
        }
        return $news;
    }

    public function GetUpcomingEvents()
    {
        $events = array();
        $dt = new DateTime("now");
        $curdate = $dt->getTimestamp();

        if ($this->super)
        {
            $events = Event::FindUpcoming($curdate);
        }
        else
        {
            $abilities = User::Find(
                "ability",
                "user_id = ? and level > ?",
                array($this->id, Ability::EDIT));

            foreach ($abilities as $ability)
            {
                $events = array_merge(
                    $events,
                    Event::FindUpcoming($curdate, $ability->entity_type, $ability->entity_id));
            }
        }
        return $events;
    }

    public function GetExpiredEvents()
    {
        $events = array();
        $dt = new DateTime("now");
        $curdate = $dt->getTimestamp();

        if ($this->super)
        {
            $events = Event::FindExpired($curdate);
        }
        else
        {
            $abilities = User::Find(
                "ability",
                "user_id = ? and level > ?",
                array($this->id, Ability::EDIT));

            foreach ($abilities as $ability)
            {
                $events = array_merge(
                    $events,
                    Event::FindExpired($curdate, $ability->entity_type, $ability->entity_id));
            }
        }
        return $events;
    }

    // return the name and id for programs that user can modify news for
    public function OptionsForEntitiesWithPrivilege($entity_types, $level = Ability::ADMIN, $conditions = 1, $values = array())
    {
        if (!is_array($entity_types))
        {
            if ($entity_types == 'all')
            {
                $entity_types = array("Program", "Team", "PlugIn");
            }
            else
            {
                $entity_types = explode(",", preg_replace("/\s/", '', $entity_types));
            }
        }
        $entity_arr = array();
        $entities = array();
        if ($this->super)
        {
            foreach ($entity_types as $type)
            {
                $types = Pluralize(SnakeCase($type));
                $$types = User::Find($type, $conditions, $values);
                $entities = array_merge($entities, $$types);
            }
            foreach ($entities as $entity)
            {
                $entity_arr["{$entity}:{$entity->id}"] = "{$entity}-{$entity->name}";
            }
        }
        else
        {
            $abilities = User::Find(
                "Ability",
                "user_id = ? and level = ?",
                array($this->id, $level));
            foreach ($abilities as $ability)
            {
                if (in_array($ability->entity_type, $entity_types))
                    $entity_arr["{$ability->entity_type}:{$ability->entity_id}"] = "{$ability->entity_type}-{$ability->entity_name}";
            }
        }
        return $entity_arr;
    }

    public function Notify($subject, $message, $data=array())
    {
        global $log;
        $log->logDebug("User::Notify() notification_type for user: {$this->notification_type}");
        $ok = true;
        switch ($this->notification_type)
        {
        case User::EMAIL_AND_MESSAGE:
            // intentional fall-through
            $log->logDebug("sending message");
            $this->SendMessage($subject, $message, $data);
        case User::EMAIL:
            $log->logDebug("sending email");
            $ok = SendSiteEmail($this, $subject, $message, $data);
            break;
        case User::MESSAGE:
            $log->logDebug("sending message");
            $this->SendMessage($subject, $message, $data);
            break;
        default:
            break;
        }

        if (!$ok)
        {
            global $log;
            $log->logInfo("Failed to send email to {$user->login}");
        }
    }

    public function SendMessage($subject, $message, $data=array())
    {
        global $APP_BASE_PATH;
        ob_start();
        require_once $APP_BASE_PATH . "/emails/" . SnakeCase($message) . ".phtml";
        $message = ob_get_contents();
        ob_end_clean();
        $msg = User::Create('Message');
        $msg->title = $subject;
        $msg->body = $message;
        $msg->user_id = $this->id;
        $msg->unread = true;
        $msg->Save();
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
