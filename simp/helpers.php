<?
global $BASE_PATH;
if (file_exists($BASE_PATH . "app/helpers.php"))
{
    require_once $BASE_PATH . "app/helpers.php";
}

// leaving these in the global namespace.
class Path
{
    public static function __callStatic($name, $arguments)
    {
        global $REL_PATH;
        //global $log;
        //$log->logDebug("Path::{$name}({$arguments[0]})");
        $name_arr = explode('_', $name);
        $params = array();
        if (count($arguments) > 0)
        {
            //$fields = array_merge($fields, $arguments);
            foreach ($arguments as $name)
            {
                if (strpos($name, "=") != false)
                {
                    $params[] = $name;
                }
                else {
                    $name_arr[] = SnakeCase($name);
                }
            }
        }
        $path = $REL_PATH . implode('/', $name_arr);
        if (count($params) > 0)
        {
            $p = implode("&", $params);
            $path .= "?" . $p;
        }
        return $path;
    }

    public static function home()
    {
        global $REL_PATH;
        return $REL_PATH;
    }

    public static function Relative($path)
    {
        global $REL_PATH;
        return "{$REL_PATH}{$path}";
    }
}

function h($str)
{
  return htmlentities($str, ENT_QUOTES);
}

function FormTag($name, $method, $action = NULL, $can_upload = false)
{
    $html = "<form name=\"{$name}\" method=\"post\"";
    if (isset($action)) $html .= " action=\"{$action}\"";
    if ($can_upload == true) $html .= " enctype=\"multipart/form-data\"";
    $html .= ">\n";
    switch ($method)
    {
    case \simp\Request::DELETE:
        $value = 'delete';
        break;
    case \simp\Request::PUT:
        $value = 'put';
        break;
    case \simp\Request::GET:
        $value = 'get';
        break;
    }
    if (isset($value)) $html .= "\n\t<input type=\"hidden\" name=\"method\" value=\"{$value}\"/>";
    return $html;
}

function EndForm()
{
    return "</form>";
}

/// Creates a text field for a model
/// @param $model Model to use
/// @param $field field in model
/// @param $opts assoc array with options for the text field
///     valid options:
///         'id' => html id field of input
///         'class' => html class field of input
///         'parent' => parent model for $model
///         'array' => true if field is to be treated as element of array
///         'size' => size of text field
function TextField($model, $field, $opts = array()) //$size = "20", $class = NULL, $id = NULL)
{
    $attrs = GetInputAttributes($model, $field, $opts);

    $html = "<input type=\"text\" name=\"{$attrs['name']}\"";
    $html .= $attrs['id'];
    if (array_key_exists('class', $attrs)) $html .= $attrs['class'];
    $html .= $attrs['size'];
    $html .= " value=\"{$attrs['value']}\"";
    $html .= "/>";
    return $html; 
}

function DatePicker($model, $field, $opts = array())
{
    $attrs = GetInputAttributes($model, $field, $opts);

    $errors = $model->GetErrors();
    $error_class = "";
    //print_r($field);
    //print_r($errors);
    if (array_key_exists($field, $errors))
    {
        $error_class = "error";
    }
    $html = "<input type=\"text\" name=\"{$attrs['name']}\"";
    $html .= $attrs['id'];
    $html .= " class=\"date-picker {$error_class} ";
    $html .= array_key_exists('class', $opts) ? $opts['class'] : '';
    $html .= "\"";
    $html .= $attrs['size'];
    $html .= " value=\"{$attrs['value']}\"";
    $html .= "/>";
    return $html; 
}


function PasswordField($model, $field, $opts = array())
{
    $attrs = GetInputAttributes($model, $field, $opts);

    $html = "<input type=\"password\" name=\"{$attrs['name']}\"";
    $html .= $attrs['id'];
    $html .= $attrs['class'];
    $html .= $attrs['size'];
    $html .= " value=\"{$attrs['value']}\"";
    $html .= "/>";
    return $html; 
}

function RadioButton($model, $field, $value, $html_opts = array())
{
    $attrs = GetInputAttributes($model, $field, $html_opts);
    $html = "<input type=\"radio\" name=\"{$attrs['name']}\"";
    $html .= $attrs['id'];
    $html .= $attrs['class'];
    if ($attrs['value'] == $value) $html .= " checked";
    $html .= " value=\"{$value}\">";
    return $html;
}

/// Creates a group of radio buttons for with options
/// @param $model Model to use
/// @param $field field in model
/// @param $opts assoc array with options for the text field
///     valid options:
///         'id' => html id field of input
///         'class' => html class field of input
///         'parent' => parent model for $model
///         'array' => true if field is to be treated as element of array
/// @param $wrapper callback to wrap each option
function RadioGroup($model, $field, $options, $html_opts = array(), $radio_wrapper = NULL)
{
    $attrs = GetInputAttributes($model, $field, $html_opts);
    $html = '';

    // TODO: wrap each of these appropriately?
    foreach ($options as $val)
    {
        $input = "<input type=\"radio\" name=\"{$attrs['name']}\"";
        $input .= $attrs['id'];
        $input .= $attrs['class'];
        if ($attrs['value'] == $val) $input .= " checked";
        $input .= " value=\"{$val}\">";
        if (isset($radio_wrapper))
        {
            $html .= $radio_wrapper($input);
        }
        else
        {
            $html .= $input;
        }
    }

    return $html;
}

function CheckBoxButton($model, $field, $opts = array())
{
    $attrs = GetInputAttributes($model, $field, $opts);
    $value = $attrs['value'] ? "&#x2713" : "  ";
    $html = "<input type='submit' name='{$field}' value='{$value}'";
    $html .= $attrs['id'];
    $html .= $attrs['class'];
    $html .= $attrs['size'];

    $html .= " />";
    $html .= "<input type='hidden' name='{$attrs['name']}' value='{$attrs['value']}' />";
    return $html;
    
}

function CheckBoxField($model, $field, $opts = array())
{
    $attrs = GetInputAttributes($model, $field, $opts);

    $html = "<input type='hidden' name='{$attrs['name']}' value='0'/>";
    $html .= "<input type='checkbox' name='{$attrs['name']}'";
    $html .= $attrs['id'];
    $html .= $attrs['class'];
    $html .= $attrs['size'];
    $html .= " value='1'";

    /*
    if (isset($model))
    {
        if ($model->__get($field)) $html .= " checked='true'";
    }
     */
    if ($attrs['value'] == true) $html .= " checked='true'";
    $html .= "/>";

    return $html;
}

function Wysiwyg($model, $field, $rows=3, $cols=80, $class=NULL, $id=NULL)
{
    $nclass = "wysiwyg";
    if (isset($class)) $nclass .= " $class";
    return TextArea($model, $field, $rows, $cols, $nclass, $id);
}


function TextArea($model, $field, $rows = 3, $cols = 80, $class = NULL, $id = NULL)
{
    $value = "";
    $html = "<textarea";
    $error_class = '';
    if (isset($id)) $html .= " id=\"{$id}\"";
    if (isset($model))
    {
        $mname = $model;
        $errors = $model->GetErrors();
        if (array_key_exists($field, $errors))
        {
            $error_class =  " error";
        }
        $input_field = $mname . "[{$field}]";
        $html .= " name=\"$input_field\"";
        $value = $model->__get($field);
    }
    $html .= " class=\"{$class}{$error_class}\"";
    $html .= " rows=\"${rows}\" cols=\"${cols}\">$value</textarea>\n";
    return $html;
}

function SimpleSelect($model, $field, $options, $opts = array())
{
    $attrs = GetInputAttributes($model, $field, $opts);
    $html = "<select";
    $html .= $attrs['id'];
    $html .= $attrs['class'];
    $html .= " name=\"{$attrs['name']}\"";
    $html .= ">\n";
    foreach($options as $val => $text)
    {
        if (is_array($text))
        {
            $html .= "\t<optgroup label=\"$val\">\n";
            foreach ($text as $val2 => $text2)
            {
                $text = HumanCase($text2);
                $html .= "\t<option value=\"{$val2}\"";
                if ($val2 == $attrs['value']) $html .= " selected=\"selected\"";
                $html .= ">{$text2}</option>\n";
            }
            $html .= "\t</optgroup>\n";

        }
        else
        {
            $text = HumanCase($text);
            $html .= "\t<option value=\"{$val}\"";
            if ($val == $attrs['value']) $html .= " selected=\"selected\"";
            $html .= ">{$text}</option>\n";
        }
    }
    $html .= "</select>";
    return $html;
}

function FileSelect($model, $field, $opts = array())
{
    $attrs = GetInputAttributes($model, $field, $opts);
    $html = "<input type=\"file\"";
    $html .= $attrs['id'];
    $html .= $attrs['class'];
    $html .= " name=\"{$field}\" \>";
    if (isset($attrs['value'])) $html .= " currently: {$attrs['value']}";
    return $html;
}

function rand_str($length = 10)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $str = "";
    for ($i = 0; $i<$length; $i++)
    {
        $str .= $chars{mt_rand(0, strlen($chars)-1)};
    }
    return $str;
}
    
function l($text, $path, $opts = array())
{
    global $REL_PATH;
    //$pathstr = $REL_PATH;
    $class = '';
    $target = '';

    if (isset($opts['class']))
    {
        $c = $opts['class'];
        $class = "class='$c'";
    }
    if (isset($opts['target']))
    {
        $t = $opts['target'];
        $target = "target='$t'";
        if ($t == 'blank')
        {
            $text .= "<img style=\"vertical-align:bottom;\" src=\"$REL_PATH/resources/img/link_go.png\" />";
        }
    }
    if (isset($opts['external']) && $opts['external'] == true)
    {
        $target = $target == '' ? "target='blank'" : $target;
        if (strpos($path, "http://") === false) $path = "http://" . $path;
        $text .= "<img style=\"vertical-align:bottom;\" src=\"$REL_PATH/resources/img/link_go.png\" />";
    }
    if (isset($opts['method']))
    {
        $method = $opts['method'];
        $name = rand_str();
        $html = <<<HERE
        <form style="display:none;" name="$name" method="post" action="$path">
            <div style="display:none;">
            <input type="hidden" name="method" value="$method"/>
            <script type="text/javascript">function submit_$name() { document.$name.submit();}</script>
            </div>
        </form>
        <a href="javascript:submit_$name();">$text</a>
HERE;
   
    }
    else
    {
        $html = "<a href='$path' $class $target>$text</a>";
    }
    
    return $html;
}

function ObsEmailLink($email)
{
    $parts = explode("@", $email);
    //$domain = explode(".", $parts[1]);
    $html = <<<EOD
<span id='email_obs_$parts[0]'>$parts[0]@<del>REMOVE</del>$parts[1]</span>
<script type='text/javascript'>
<!--
    var name = "$parts[0]";
    var at = "@";
    var domain = "$parts[1]";
    $('span#email_obs_$parts[0]').replaceWith("<a href='" + "mail" + "to:" + name + at + domain + "'>" + name + at + domain + "</a>");
-->
</script>
EOD;
    return $html;
}

function FormatDateTime($timestamp, $format = NULL)
{
    if (!$timestamp)
    {
        return "never";
    }
    $dt = new DateTime();
    $dt->setTimestamp($timestamp);
    if (!isset($format))
    {
        $format = "Y/m/d @ H:i:s T";
    }
    return $dt->format($format);

}

function TimeAgoInWords($timestamp, $two_fields = false)
{
    $cur_tm = time(); 

    $dif = $cur_tm-$timestamp;

    $pds = array('second','minute','hour','day','week','month','year','decade');

    $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);

    for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);

    $no = floor($no); if($no <> 1) $pds[$v] .='s'; $x=sprintf("%d %s ",$no,$pds[$v]);
    if(($two_fields == true)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= TimeAgoInWords($_tm);
    return $x;
}


function GetErrorsFor($model)
{
    $html = "";
    $errors = $model->GetErrors();
    if (count($errors) > 0)
    {
        $html = "\t<div class=\"errors\">\nPlease correct the following:";
        $html .= "\t\t<ol>\n";
        foreach ($errors as $error)
        {
            $html .= "\t\t\t<li>{$error}</li>\n";
        }
        $html .= "\t\t</ol>\n";
        $html .= "\t</div>\n";
    }
    return $html;
}

function GetInputAttributes($model, $field, $opts)
{
    $newopts = array();
    $newopts['class'] = "";
    $newopts['size'] = " size=\"" . (isset($opts['size']) ? $opts['size'] : "20") . "\"";
    $newopts['rows'] = " rows=\"" . (isset($opts['rows']) ? $opts['rows'] : 3) . "\"";
    $newopts['cols'] = " cols=\"" . (isset($opts['cols']) ? $opts['cols'] : 80) . "\"";
    $newopts['id'] = isset($opts['id']) ? " id=\"{$opts['id']}\"" : "";
    $error_class = "";

    if (isset($model)) 
    {
        $mname = isset($opts['parent']) ? $opts['parent'] . "[{$model}]" : $model;
        $newopts['name'] = array_key_exists('array', $opts) ?
            "{$mname}[{$field}][{$opts['array']}]" :
            "{$mname}[{$field}]";
        //if (is_subclass_of($model, "\simp\Model"))
        if (is_object($model))
        {
            if (is_array($model->$field)) 
            {
                $arr = $model->$field;
                $newopts['value'] = $arr[$opts['array']];
                $errors = $model->GetErrors();
                $efield = "{$field}[{$opts['array']}]";
                if (array_key_exists($efield, $errors))
                {
                    $error_class = " error"; 
                }
            }
            else
            {
                $newopts['value'] = $model->__get($field);
                $errors = $model->GetErrors();
                if (array_key_exists($field, $errors))
                {
                    $error_class = " error";
                }
            }
        }
        else
        {
            $newopts['value'] = isset($opts['value']) ? $opts['value'] : '';
        }
    }
    else
    {
        // TODO: change this
        $newopts['name'] = isset($opts['array']) ?
            "{$opts['array']}[$field]" :
            "$field";
    }
    $input_class = isset($opts['class']) ? $opts['class'] : "";
    if ($input_class != "" || $error_class != "")
    {
        $newopts['class'] = " class=\"{$input_class}{$error_class}\"";
    }
    return $newopts;
}

function GetURL()
{
    return $_SESSION['url'];
}

function SetReturnURL($url)
{
    global $log;
    $url = Path::home() . $url;
    $log->logDebug("helper: setting return_to: $url");
    $_SESSION['return_to'] = $url;
}

function GetReturnURL()
{
    return isset($_SESSION['return_to']) ? 
      $_SESSION['return_to'] :
      Path::home();
    //return $_SESSION['return_to'];
}

function CheckHistory($url)
{
    $url = Path::Relative($url);
    $prev = $cur = NULL;
    if (array_key_exists('history', $_SESSION))
    {
        $cur = end($_SESSION['history']);
        $prev = prev($_SESSION['history']);
        global $log;
        $log->logDebug("url: $url cur: $cur prev:$prev");
        reset($_SESSION['history']);
    }
    if ($cur != $url)
    {
        if ($prev == $url) {PopHistory(); PopHistory();}
        else PushHistory($url);
    }
}

function PushHistory($url)
{
    global $log; $log->logDebug("PushHistory($url)");
    if (!array_key_exists('history', $_SESSION)) $_SESSION['history'] =  array();
    $_SESSION['history'][] = $url;
}

function GetBackURL()
{
    $back = Path::home();
    if (array_key_exists('history', $_SESSION)) 
    {
        $back = end($_SESSION['history']);
    }
    return $back;
}

function GetHistory()
{
    if (array_key_exists('history', $_SESSION)) 
    {
        return $_SESSION['history'];
    }
    return array();
}

function PopHistory()
{
    if (array_key_exists('history', $_SESSION)) 
    {
        return array_pop($_SESSION['history']);
    }
    return false;
}

function AddFlash($flash)
{
    $_SESSION['flash'][] = $flash;
}

function AddError($field, $error)
{
  $_SESSION['error'][$field] = $error;
}

function GetFlash()
{
    $flashstr = "";
    if (isset($_SESSION['flash']))
    {
        $flashar = $_SESSION['flash'];
        unset($_SESSION['flash']);
        if (is_array($flashar))
        {
            $flashstr = "<div class='flash'><ul class='flash'>\n";
            foreach($flashar as $flash)
            {
                $flashstr .= "  <li>$flash</li>\n";
            }
            $flashstr .= "</ul></div>";
        }
    }
    return $flashstr;
}

function GetErrors()
{
  $errorar = $_SESSION['error'];
  unset($_SESSION['error']);
  if (is_array($errorar))
  {
    $errorstr = "<div id='error'><ol class='error'>\n";
    foreach($errorar as $field => $error)
    {
      $errorstr .= "  <li>$field: $error</li>\n";
    }
    $errorstr .= "</ol></div>";
  }
  return $errorstr;
}

function HasError($field)
{
  return (isset($_SESSION['error'][$field]));
}

function SetAuthorizedUser($id)
{
  $_SESSION['user'] = $id;
}

function ClearSession()
{
  $_SESSION['user'] = null;
}

function IsLoggedIn()
{
  return (array_key_exists('user', $_SESSION) && $_SESSION['user'] != NULL);
}

function CurrentUser()
{
    return \simp\Model::FindById("User", $_SESSION['user']);
}

function GetBasePath()
{
  global $REL_PATH;
  return $REL_PATH;
}

function GetResourcePath()
{
    global $REL_PATH;
    return $REL_PATH . "resources/";
}

function GetCSSPath($filename)
{
  return (GetResourcePath() . "css/" . $filename);
}

function GetImagePath($filename)
{
  return (GetResourcePath() . "img/" . $filename);
}

function GetJSPath($filename)
{
    return (GetResourcePath() . "js/" . $filename);
}

function IncludeCSS($filename, $media = "screen")
{
    $css = "<link rel=\"stylesheet\" type=\"text/css\" href=\"";
    $css .= GetCSSPath($filename);
    $css .= "\" media=\"$media\" />";
    return $css;
}

function IncludeJS($filename)
{
    $js_include = '<script type="text/javascript" src="';
    $js_include .= GetJSPath($filename);
    $js_include .= '"></script>' . "\n";
    return $js_include;
}

