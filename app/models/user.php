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

    public static function GetPrivilegedUsers($entity_type, $entity_id, $level=0)
    {
        $users = array();
        $q = "select ability.level, user.* from user, ability ";
        $q .= "where user.id = ability.user_id and ability.entity_type = ? and ability.entity_id = ?";
        if ($level > 0)
        {
            $q .= "and ability.level = ?";
        }
        $q .= " order by ability.level, user.first_name";

        $result = \R::getAll($q, array(SnakeCase($entity_type), $entity_id));
        return $result;
    }

    public function FindEntitiesWithPrivilege($entity_type, $level=Ability::ADMIN)
    {
        $etype = SnakeCase($entity_type);
        if ($this->super)
        {
            return self::FindAll($etype);
        }
        $q = "select $etype.* from ability, $etype ";
        $q .= "where ability.user_id = ? and ability.level >= ? and $etype.id = ability.entity_id";

        $result = \R::getAll($q, array($this->id, $level));
        return \R::convertToBeans($etype, $result);
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
                //$log->logDebug("UpdateAbilities: ability[{$vals['id']}]: \n" . print_r($ability, true));
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

    public function CanAccessAny($entity_type, $level)
    {
        $entity_type = SnakeCase($entity_type);
        if ($this->super) return true;
        $abilities = User::Find(
            "Ability",
            "user_id = ? and entity_type = ?",
            array($this->id, $entity_type));
        $can = false;
        foreach ($abilities as $ability)
        {
            if ($ability->level >= $level)
            {
                $can = true;
            }
        }
        return $can;
    }

    public function CanAccess($entity_type, $entity, $level, $by_name = false)
    {
        global $log;
        $entity_type = SnakeCase($entity_type);
        $log->logDebug("CanAccess: checking $entity_type, $entity, $level");
        if ($this->super) return true;
        $conditions = "user_id = ? and entity_type = ? and ";
        $conditions .= $by_name === true ? "entity_name = ?" : "entity_id = ?";
        $ability = User::FindOne(
            "Ability", 
            $conditions,
            array($this->id, $entity_type, $entity));
        //echo "ability: " . print_r($ability, true);
        if ($ability)
        {
            $log->logDebug("CanAccess: found {$ability->level}");
            return $ability->level >= $level;
        }
        return false;
    }

    public function CanEdit($entity_type, $entity, $by_name = false)
    {
        return $this->CanAccess($entity_type, $entity, Ability::EDIT, $by_name);
    }

    public function CanPublish($entity_type, $entity, $by_name = false)
    {
        return $this->CanAccess($entity_type, $entity, Ability::PUBLISH, $by_name);
    }

    public function CanAdmin($entity_type, $entity, $by_name = false)
    {
        return $this->CanAccess($entity_type, $entity, Ability::ADMIN, $by_name);
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
            // TODO: check for matching email as well
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

    public function GetEntitiesWithPrivilege($level=Ability::ADMIN, $except)
    {
        if (!is_array($except))
        {
            $except = explode(",", preg_replace("/\s/", '', $except));
        }

        foreach($except as &$type) $type = SnakeCase($type);
        unset($type);

        // find all entity_ids of all entity_types then do ability check
        $types = array_diff(array('main', 'program', 'plug_in', 'team'), $except);
        $placeholders = rtrim(str_repeat('? ,', count($types)), ',');

        $entities = array();
        if ($this->super)
        {
            foreach ($types as $type)
            {
                if ($type == "main")
                {
                    $entities[] = array(
                        'type' => "Main",
                        'id' => "0",
                        'name' => "Club"
                    );
                }
                else
                {
                    $entity_ar = User::FindAll($type);
                    foreach ($entity_ar as $entity)
                    {
                        $entities [] = array(
                            'type' => ClassCase($type),
                            'id' => $entity->id,
                            'name' => $entity->name
                        );
                    }
                }
            }
        }
        else
        {
            //$types = implode(",", $types);
            global $log;
            $vals = array_merge(array($this->id, $level), $types);
            //$log->logDebug("User::GetEntitiesWithPrivilege() vals = " . print_r($vals, true));

            //\R::debug(true);
            $abilities = User::Find(
                'Ability', 
                "user_id = ? and level >= ? and entity_type in ($placeholders)", 
                $vals);
            //\R::debug(false);
            $log->logDebug("User::GetEntitiesWithPrivilege() abilities = " . print_r($abilities, true));

            foreach($abilities as $ability)
            {
                $entities[] = array(
                    'type' => $ability->entity_type,
                    'id' => $ability->entity_id, 
                    'name' => $ability->entity_name);
            }
        }
        //print_r($entities);
        return $entities;
            
    }

    // return the name and id for programs that user can modify news for
    public function OptionsForEntitiesWithPrivilege($entity_types, $level = Ability::ADMIN, $conditions = 1, $values = array())
    {
        if (!is_array($entity_types))
        {
            if ($entity_types == 'all')
            {
                $entity_types = array("Main", "Program", "Team", "PlugIn");
            }
            else
            {
                $etypes = preg_replace("/\s/", '', $entity_types);
                $entity_types = explode(",", $etypes);
            }
        }
        $entity_arr = array();
        $entities = array();
        if ($this->super)
        {
            foreach ($entity_types as $type)
            {
                // don't try, no database table for this guy!
                if ($type == "Club") continue;
                $types = Pluralize(SnakeCase($type));
                $$types = User::Find($type, $conditions, $values);
                $entities = array_merge($entities, $$types);
            }
            foreach ($entities as $entity)
            {
                $entity_arr["{$entity}:{$entity->id}"] = "{$entity}-{$entity->name}";
            }
            if (in_array("Main", $entity_types))
            {
                $entity_arr["Main:0"] = "Main-Club";
            }
        }
        else
        {
            $abilities = User::Find(
                "Ability",
                "user_id = ? and level >= ?",
                array($this->id, $level));
            foreach ($abilities as $ability)
            {
                $etype = ClassCase($ability->entity_type);
                if (in_array($etype, $entity_types))
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
        global $_SERVER;
        if (!is_array($data))
        {
            $data = array('data' => $data);
        }
        $host = GetCfgVar('site_address');
        if ($host == "") $host = $_SERVER['SERVER_NAME'];
        $data = array_merge($data, array(
            'site_name' => GetCfgVar('site_name'),
            'host' => $host,
            'user' => $this
            )
        );
        $msg_path = $APP_BASE_PATH . "/emails/" .SnakeCase($message) . ".phtml";
        global $log; $log->logDebug("sending message using template: $msg_path");
        ob_start();
        include $msg_path;
        $body = ob_get_contents();
        ob_end_clean();
        $msg = User::Create('Message');
        $msg->title = $subject;
        $msg->body = $body;
        $msg->user_id = $this->id;
        $msg->unread = true;
        $msg->date = strtotime("now");
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
