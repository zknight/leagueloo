<?php
namespace simp;

class BaseModel
{

    protected $_errors;
    protected $_skip_sanity;

    public static function LoadModel($classname)
    {
        global $log;
        global $APP_BASE_PATH;
        $filename = SnakeCase($classname) . ".php";
        $path = $APP_BASE_PATH . "/models/" . $filename;
        $module_path = $APP_BASE_PATH . "/modules/";
        $log->logDebug("attempting to find $classname @ $path");
        if (file_exists($path))
        {
            //echo("attempting to find $classname @ $path");
            require_once $path;
        }
    }

    public function __construct()
    {
        $this->_errors = array();
        $this->_skip_sanity = array();
    }

    public function __toString()
    {
        return get_class($this);
    }

    protected function SkipSanity()
    {
        if (func_num_args() > 0)
        {
            $args = func_get_args();
            foreach ($args as $arg)
            {
                $this->_skip_sanity[$arg] = true;
            }
        }
    }

    public function GetErrors()
    {
        return $this->_errors;
    }

    public function SetError($field, $error)
    {
        $this->_errors[$field] = $error;
    }

    public function Save()
    {
        return $this->BeforeSave();
    }

    public function Check()
    {
        return $this->BeforeSave();
    }

    // will automatically sanitize inputs unless excepted
    public function UpdateFromArray($vars)
    {
        if (empty($vars)) return;
        foreach ($vars as $name => $val)
        {
            //$val = stripslashes(html_entity_decode($val));
            if (is_string($val))
            {
                if (!array_key_exists($name, $this->_skip_sanity) || $this->_skip_sanity[$name] == false)
                {
                    // sanitize to further prevent sql injection/xss on simple inputs
                    $val = preg_replace("`[^a-zA-Z0-9@'.,$?&=#:_/() -]`", '', $val);
                }
            }
            if ($name != "id") $this->$name = $val;
        }
        $this->AfterUpdate();
    }

    //// Event CALLBACKS
    // callback for when model is opened (find, load)
    public function OnLoad()
    {
    }

    // called after fields are updated from an array
    public function AfterUpdate()
    {
    }

    public function BeforeSave()
    {
        return true;
    }

    public function AfterSave()
    {
        return true;
    }

    public function AfterFirstSave()
    {
        return true;
    }

    public function BeforeDelete()
    {
        return true;
    }

    public function AfterDelete()
    {
        return true;
    }

    // verification methods
    protected function HasErrors()
    {
        return (count($this->_errors) > 0);
    }

    protected function GetFieldForVerify($field)
    {
        $matches = array();
        if (preg_match("/(\w+)\[(.+)\]/", $field, $matches))
        {
            echo "GetFieldForVerify is array";
            $field = $matches[1];
            $i = $matches[2];
            $arr = $this->$field;
            return $arr[$i];
        }
        return $this->$field;
    }

    protected function VerifyEmail($field, $errmsg = NULL)
    {
        $ok = true;
        if (!filter_var($this->$field, FILTER_VALIDATE_EMAIL))
        {
            $msg = $errmsg == NULL ? "Please enter a valid email address." : $errmsg;
            $this->SetError($field, $msg);
            $ok = false;
        }
        return $ok;
    }

    protected function VerifyArraySize($field, $size, $errmsg = NULL)
    {
        $ok = true;
        if (count($this->$field) !== $size)
        {
            $msg = $errmsg == NULL ? "{$field} must be an array of $size." : $errmsg;
            $this->SetError($field, $msg);
            $ok = false;
        }
        return $ok;
    }

    protected function VerifyMaxLength($field, $length, $errmsg = NULL)
    {
        $ok = true;
        if (strlen($this->$field) > $length)
        {
            $msg = $errmsg == NULL ? "{$field} must be no longer than {$length} characters." : $errmsg;
            $this->SetError($field, $msg);
            $ok = false;
        }
        return $ok;
    }

    protected function VerifyMinLength($field, $length, $errmsg = NULL)
    {
        $ok = true;
        $val = $this->GetFieldForVerify($field);
        if (strlen($val) < $length)
        {
            $msg = $errmsg == NULL ? "{$field} must be at least {$length} characters." : $errmsg;
            $this->SetError($field, $msg);
            $ok = false;
        }
        return $ok;
    }

    protected function VerifyNotEmpty($field, $errmsg = NULL)
    {
        $ok = true;
        $val = $this->$field;
        if (empty($val))
        {
            $msg = $errmsg == NULL ? "{$field} must not be blank." : $errmsg;
            $this->SetError($field, $msg);
            $ok = false;
        }
        return $ok;
    }

    protected function VerifyValidDate($field)
    {
        // valid dates are mm/dd/yyyy or mm-dd-yyyy
        $ok = true;
        if (preg_match('~(\d{1,2})[/:.,_\-\' ](\d{1,2})[/:.,_\-\' ](\d{4})~', $this->$field, $match))
        {
            if (checkdate($match[1], $match[2], $match[3]) == false)
            {
                $ok = false;
            }
        }
        else $ok = false;

        if ($ok == false)
        {
            $msg = "{$field} must be a valid date (mm/dd/yyyy)";
            $this->SetError($field, $msg);
        }
        return $ok;
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
            $this->SetError($field, "Date must be in format: mm/dd/yyyy or mm-dd-yyyy");
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
            $this->SetError($field, "Time must be in format: (24 hour) hh:mm:ss or (12 hour) hh:mm:ss [am/pm]");
            $retval = false;
        }
        return $retval;
    }

    protected function VerifyNotEqual($field, $value)
    {
        $ok = true;
        if ($this->$field === $value)
        {
            $msg = $errmsg == NULL ? "{$field} must not be $value" : $errmsg;
            $this->SetError($field, $msg);
            $ok = false;
        }
        return $ok;
    }
}

