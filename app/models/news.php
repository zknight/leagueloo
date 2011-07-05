<?
/// News model
///
/// A news article belongs to a program or team.
/// fields:
///     short_title: short title used in url
///     title: news article title
///     intro: introductory text for listing on program page
///     body: body of article, added to intro
///     created_on: date/time of article creation
///     updated_on: date/time of article update
///     publish_on: date/time to publish article
///     expiration: publication expiration, article will not
///         be displayed after this date/time
///     editor: user id of last editor ** move this to editors table?
///     publisher: user id of publisher ** move this to publishers table?
///     entity_type: program or team
///     entity_id: id of entity owner
///     entity_name: name of entity
class News extends \simp\Model
{
    private $_pub_date;
    private $_pub_time;
    private $_pub_now;
    private $_exp_date;
    private $_exp_time;
    private $_break_tags;
    public $file_info;
    protected $rel_path;
    protected $abs_path;
    public $img_path;

    public function Setup()
    {
        $this->img_path = NULL;
        $this->file_info = NULL;
        global $REL_PATH;
        global $BASE_PATH;
        $path = "resources/files/img/";
        $this->rel_path = $REL_PATH . $path;
        $this->abs_path = $BASE_PATH . $path;
        $this->_pub_date = "12/31/2037";
        $this->_pub_time = "00:00:00";
        $this->_pub_now = false;
        $this->_exp_date = "12/31/2037";
        $this->_exp_time = "00:00:00";
        $this->_break_tags = array(
            "</p>" => 3,
            "</tr>" => 4,
            "<br>" => 3,
            "<br />" => 5,
            "<br/>" => 4,
            ". " => 1,
            "? " => 1,
        );

        $this->SkipSanity("body");
    }    

    public function OnLoad()
    {
        $this->_pub_date = FormatDateTime($this->publish_on, "m/d/Y");
        $this->_pub_time = FormatDateTime($this->publish_on, "H:i:s");
        $this->_exp_date = FormatDateTime($this->expiration, "m/d/Y");
        $this->_exp_time = FormatDateTime($this->expiration, "H:i:s");
        if (!$this->image)
        {
            global $REL_PATH;
            $this->img_path = $REL_PATH . "resources/files/img/{$this->entity_type}/{$this->entity_name}/";
            $this->img_path .= \R::getCell(
                "select image from " . SnakeCase($this->entity_type) . " where id = ?",
                array($this->entity_id));
        }
        else
        {
            $this->img_path = $this->rel_path . "news/{$this->image}";
        }
    }

    public function GetPath()
    {
        return "{$this->entity_name}/{$this->short_title}";
    }

    public static function FindWithShortTitleByEntityName(
        $short_title,
        $entity_type,
        $entity_name)
    {
        $entity_table = SnakeCase($entity_type);
        $q = "";
        if ($entity_name == "Club")
        {
            $article = News::FindOne(
                'News', 
                'entity_type = ? and short_title = ?',
                array("Main", $short_title)
            );

            return $article;
        }
        else
        {
            $q = "select news.* from news, {$entity_table} ";
            $q .= "where {$entity_type}.name like ? ";
            $q .= "and news.entity_type like ? ";
            $q .= "and news.entity_id = {$entity_type}.id ";
            $q .= "and news.short_title = ? ";
            $q .= "limit 1";
            $result = \R::getRow(
                $q,
                array($entity_name, $entity_type, $short_title)
            );
            if (isset($result))
            {
                $bean = \R::dispense("news");
                $bean->import($result);
                return new News($bean);
            }
        }
        return null;
    }

    public static function FindPublished($owner_type = NULL, $owner_id = NULL, $conditions = NULL, $vals = array())
    {
        $dt = new DateTime("now");
        $curdate = $dt->getTimestamp();
        $params = "publish_on <= ? and expiration >= ?";
        $values = array($curdate, $curdate);
        if (isset($owner_type))
        {
            $params .= " and entity_type = ?";
            $values[] = $owner_type;
        }
        if (isset($owner_id))
        {
            $params .= " and entity_id = ?";
            $values[] = $owner_id;
        }
        if (isset($conditions))
        {
            $params .= " and $conditions";
            $values = array_merge($values, $vals);
        }

        return News::Find("news", $params, $values);
    }

    public static function FindUnpublished($owner_type = NULL, $owner_id = NULL)
    {
        $dt = new DateTime("now");
        $curdate = $dt->getTimestamp();
        $params = "(publish_on > ? or expiration < ?)";
        $values = array($curdate, $curdate);
        if (isset($owner_type))
        {
            $params .= " and entity_type = ?";
            $values[] = $owner_type;
        }
        if (isset($owner_id))
        {
            $params .= " and entity_id = ?";
            $values[] = $owner_id;
        }
        return News::Find("news", $params, $values);
    }

    public function __get($property)
    {
        switch ($property)
        {
            case 'publish_date':
                return $this->_pub_date;
                break;
            case 'publish_time':
                return $this->_pub_time;
                break;
            case 'publish_now':
                return $this->_pub_now;
                break;
            case 'expire_date':
                return $this->_exp_date;
                break;
            case 'expire_time':
                return $this->_exp_time;
                break;
            case 'published':
                $curdate = new DateTime("now");
                if ($curdate->getTimestamp() >= $this->publish_on && 
                    $curdate->getTimestamp() <= $this->expiration)
                {
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            case 'entity_name':
                if ($this->entity_id == 0)
                {
                    return "Club";
                }
                return \R::getCell(
                    "select name from " . SnakeCase($this->entity_type) . " where id = ?", 
                    array($this->entity_id));
                break;
            case "entity_designator":
                $entity_designator = "{$this->entity_type}:{$this->entity_id}";
                global $log; $log->logDebug("returning entity_designator: $entity_designator");
                return $entity_designator;
                break;
            default:
                return parent::__get($property);
        }
    }

    public function __set($property, $value)
    {
        switch ($property)
        {
            case 'publish_date':
                $this->_pub_date = $value;
                break;
            case 'publish_time':
                $this->_pub_time = $value;
                break;
            case 'publish_now':
                $this->_pub_now = $value;
                break;
            case 'expire_date':
                $this->_exp_date = $value;
                break;
            case 'expire_time':
                $this->_exp_time = $value;
                break;
            case "entity_designator":
                global $log;
                $log->logDebug("setting entity_designator from: $value");
                list($this->entity_type, $this->entity_id) = explode(":", $value);
                $log->logDebug("entity_type: {$this->entity_type}");
                $log->logDebug("entity_id: {$this->entity_id}");
                break;
            default:
                return parent::__set($property, $value);
                break;
        }
    }

    public function BeforeSave()
    {
        $errors = 0;

        $this->SplitIntro();
        if (!$this->VerifyDateFormat('publish_date', $this->_pub_date)) $errors++;
        if (!$this->VerifyTimeFormat('publish_time', $this->_pub_time)) $errors++;
        if (!$this->VerifyDateFormat('expire_date', $this->_exp_date)) $errors++;
        if (!$this->VerifyTimeFormat('expire_time', $this->_exp_time)) $errors++;
        /*
        if (strlen($this->short_title) > 32) 
        {
            $errors++;
            $this->SetError('short_title', "Short title must be less than 30 characters");
        }
        */
        // TODO add functions to validate/sanitize strings

        if ($errors == 0)
        {
            if ($this->_pub_now)
            {
                $dt = new \DateTime("now");
                $this->publish_on = $dt->getTimestamp();
            }
            else 
            {
                $dt = new \DateTime("{$this->_pub_date} {$this->_pub_time}");
                $this->publish_on = $dt->getTimestamp();
            }

            if ($this->_exp_date != "")
            {
                $dt = new \DateTime("{$this->_exp_date} {$this->_exp_time}");
                $this->expiration = $dt->getTimestamp();
            }
            else
            {
                $dt = new \DateTime("12/31/2037 00:00:00");
                $this->expiration = $dt->getTimestamp();
            }

            // TODO: Generate introduction and short title from body and title, respectively

            $arr = explode(" ", strtolower($this->title));
            $short_title = preg_replace("`[^a-zA-Z0-9_]`", "_", implode("_", $arr));
            $sz = strlen($short_title);
            $this->short_title = $sz > 31 ?
                substr($short_title, 0, 31) : 
                $short_title;
        }
        if ($errors == 0)
        {
            $img_path = $this->abs_path . "news/";
            $info = $this->file_info;
            $name = NULL ;
            $err = ProcessImage(
                $info,
                $img_path,
                $name,
                array('max_height' => 315, 'max_width' => 420)
            );

            $this->image = $name;

            if ($err != false)
            {
                $errors++;
                $this->SetError("image", $err);
            }
        }
        return $errors == 0;
    }

    public function AfterFirstSave()
    {
        // find all users with access to this bad boy
        global $log;
        $log->logDebug("News::AfterFirstSave()");
        ob_start();
        \R::debug(true);
        $users = \User::FindPublishers($this->entity_type, $this->entity_id);
        \R::debug(false);
        $log->logDebug(ob_get_contents());
        ob_end_clean();
        $site_name = GetCfgVar('site_name');
        $subject = "[{$site_name}] Article Creation Notification";
        foreach ($users as $user)
        {
            $log->logDebug("attempting to send email to {$user->login}");
            $user->Notify($subject, 'new_article', array('article' => $this));
        }

        return true;
    }

    public function AfterSave()
    {
        return true;
    }

    /*
    protected function VerifyDateFormat($field, &$date)
    {
        $retval = true;
        if ($date == "")
        {
            $date = "12/31/2037";
        }
        else if (strtotime($date) == FALSE)
        {
            $this->SetError($field, "Date must be in format: mm/dd/yyyy or mm-dd-yyyy");
            $retval = false;
        }
        return $retval;
    }
     */

    protected function SplitIntro()
    {
        $max_pos = GetCfgVar('intro_chars', 600);
        $break = strpos($this->body, "<!--break-->");
        if ($break !== false)
        {
            $intro = substr($this->body, 0, $break);
        }
        else
        {
            $intro = substr($this->body, 0, $max_pos);
            $ortni = strrev($intro);
            $min_rpos = $max_pos;

            foreach ($this->_break_tags as $tag => $offset)
            {
                $pos = strpos($ortni, strrev($tag));
                if ($pos !== false)
                {
                    $min_rpos = min($pos + $offset, $min_rpos);
                }
            }

            $intro = ($min_rpos === $max_pos) || ($min_rpos === 0) ?
                $intro :
                substr($intro, 0, 0-$min_rpos);

            $tidy = new Tidy();
            $tidy->parseString(
                $intro, 
                array('show-body-only' => true),
                'utf8');
            $tidy->cleanRepair();
            $intro = $tidy->body()->value;
        }
        global $log;
        $log->logDebug("saving intro: " . print_r($intro, true));

        $this->intro = $intro;
    }

}
