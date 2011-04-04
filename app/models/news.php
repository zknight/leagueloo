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

    public function Setup()
    {
        $this->_pub_date = "12/31/2037";
        $this->_pub_time = "00:00:00";
        $this->_pub_now = false;
        $this->_exp_date = "12/31/2037";
        $this->_exp_time = "00:00:00";
    }    

    public static function FindPublished($owner_type = NULL, $owner_id = NULL)
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
                return \R::getCell(
                    "select name from " . SnakeCase($this->entity_type) . " where id = ?", 
                    array($this->entity_id));
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
            default:
                return parent::__set($property, $value);
                break;
        }
    }

    public function BeforeSave()
    {
        $errors = 0;
        
        if (!$this->VerifyDateFormat('publish_date', $this->_pub_date)) $errors++;
        if (!$this->VerifyTimeFormat('publish_time', $this->_pub_time)) $errors++;
        if (!$this->VerifyDateFormat('expire_date', $this->_exp_date)) $errors++;
        if (!$this->VerifyTimeFormat('expire_time', $this->_exp_time)) $errors++;
        if (strlen($this->short_title) > 30) 
        {
            $errors++;
            $this->_errors('short_title', "Short title must be less than 30 characters");
        }
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
                $this->expiration = $dt->getTimetstamp();
            }

            $arr = explode(" ", strtolower($this->short_title));
            $this->short_title = implode("_", $arr);
        }
        
        return $errors == 0;
    }

    protected function VerifyDateFormat($field, &$date)
    {
        $retval = true;
        if ($date == "")
        {
            $date = "12/31/2037";
        }
        else if (strtotime($date) == FALSE)
        {
            $this->_errors[$field] = "Date must be in format: mm/dd/yyyy or mm-dd-yyyy";
            $retval = false;
        }
        return $retval;
    }

    protected function VerifyTimeFormat($field, &$time)
    {
        $retval = true;
        if ($time == "")
        {
            $time = "00:00:00";
        }
        else if (strtotime($time) == FALSE)
        {
            $this->_errors[$field] = "Time must be in format: (24 hour) hh:mm:ss or (12 hour) hh:mm:ss [am/pm]";
            $retval = false;
        }
        return $retval;
    }

}
