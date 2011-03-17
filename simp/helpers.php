<?
// leaving these in the global namespace.
class Path
{
    public static function __callStatic($name, $arguments)
    {
        global $REL_PATH;
        global $log;
        $log->logDebug("Path::{$name}({$arguments[0]})");
        $name_arr = explode('_', $name);
        if (isset($arguments[0]))
        {
            $name_arr[] = $arguments[0];
        }
        $path = $REL_PATH . implode('/', $name_arr);
        return $path;
    }
}

function h($str)
{
  return htmlentities($str, ENT_QUOTES);
}

function FormTag($name, $method, $action = NULL)
{
    $html = "<form name=\"{$name}\" method=\"post\"";
    if (isset($action)) $html .= " action=\"{$action}\"";
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
    $html .= $attrs['class'];
    $html .= $attrs['size'];
    $html .= " value=\"{$attrs['value']}\"";
    $html .= "/>";
    return $html; 
}

function RadioGroup($model, $field, $options, $html_opts = array())
{
    $attrs = GetInputAttributes($model, $field, $html_opts);

    // TODO: wrap each of these appropriately?
    foreach ($options as $val)
    {
        $html = "<input type=\"radio\" name=\"{$attrs['name']}\"";
        $html .= $attrs['id'];
        $html .= $attrs['class'];
        $html .= " value=\"{$val}\">";
    }
}

function PasswordField($model, $field, $size = "20", $class = NULL)
{
  
  if (isset($model))
  {
    $err = $model->GetError($field);
    if (isset($err))
    {
      $class = 'error';
    }
  }
  $html = "<input type='password' name='" . $field . "'";
  $html .= " size='" . $size . "'";
  if ($class != NULL) 
    $html .= " class='" . $class . "'";
  if (isset($model))
    $html .= " value='" . $model->__get($field) . "'";
  $html .= "/>";
  return $html; 
}

function CheckBoxField($model, $field, $class = NULL)
{
    $input_field = $field;
    if (isset($model))
    {
        $mname = $model;
        $input_field = $mname . "[$field]";
        /*
        $err = $model->GetError($field);
        if (isset($err))
        {
          $class = 'error';
        }
         */
    }
    // The hidden field must come first
    $html = "<input type='hidden' name='$input_field' value='0'/>";
    $html .= "<input type='checkbox' name='$input_field'";

    if (isset($class))
    {
        $html .= " class='$class'";
    }
    $html .= " value='1'";
    if (isset($model))
    {
        if ($model->__get($field)) $html .= " checked='true'";
    }
    $html .= "/>";
    return $html;
}

function TextArea($model, $field, $rows = 3, $cols = 80, $class = NULL, $id = NULL)
{
    $value = "";
    $html = "<textarea";
    if (isset($class)) $html .= " class=\"{$class}\"";
    if (isset($id)) $html .= " id=\"{$id}\"";
    if (isset($model))
    {
        $mname = $model;
        $input_field = $mname . "[{$field}]";
        $html .= " name=\"$input_field\"";
        $value = $model->__get($field);
    }
    $html .= " rows=\"${rows}\" cols=\"${cols}\">$value</textarea>\n";
    return $html;
}

function SimpleSelect($model, $field, $options, $class = NULL, $id = NULL)
{
    $html = "<select";
    if (isset($class)) $html .= " class=\"{$class}\"";
    if (isset($id)) $html .= " id=\"{$id}\"";
    if (isset($model))
    {
        $mname = $model;
        $input_field = $mname . "[{$field}]";
        $html .= " name=\"$input_field\"";
        $value = $model->__get($field);
    }
    $html .= ">\n";
    foreach($options as $val => $text)
    {
        $html .= "\t<option value=\"{$val}\"";
        if ($val == $value) $html .= " selected=\"selected\"";
        $html .= ">{$text}</option>\n";
    }
    $html .= "</select>";
    return $html;
}

function rand_str($length = 10)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXyZ";
    $str = "";
    for ($i = 0; $i<$length; $i++)
    {
        $str .= $chars{mt_rand(0, strlen($chars))};
    }
    return $str;
}
    
function l($text, $path, $opts = array())
{
    global $REL_PATH;
    $pathstr = $REL_PATH;
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
    }
    if (isset($opts['method']))
    {
        $method = $opts['method'];
        $name = rand_str();
        $html = <<<HERE
        <form name="$name" method="post" action="$path">
            <input type="hidden" name="method" value="$method"/>
            <script type="text/javascript">function submit_$name() { document.$name.submit();}</script>
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
<span id='email_obs'>$parts[0]@<del>REMOVE</del>$parts[1]</span>
<script type='text/javascript'>
<!--
    var name = "$parts[0]";
    var at = "@";
    var domain = "$parts[1]";
    $('#email_obs').replaceWith("<a href='" + "mail" + "to:" + name + at + domain + ">" + name + at + domain + "</a>");
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

function GetInputAttributes($model, $field, $opts)
{
    $newopts = array();
    $newopts['size'] = " size=\"" . (isset($opts['size']) ? $opts['size'] : "20") . "\"";
    $newopts['rows'] = " rows=\"" . (isset($opts['rows']) ? $opts['rows'] : 3) . "\"";
    $newopts['cols'] = " cols=\"" . (isset($opts['cols']) ? $opts['cols'] : 80) . "\"";
    $newopts['class'] = isset($opts['class']) ? " class=\"{$opts['class']}\"" : "";
    $newopts['id'] = isset($opts['id']) ? " id=\"{$opts['id']}\"" : "";

    if (isset($model)) 
    {
        $mname = isset($opts['parent']) ? $opts['parent'] . "[{$model}]" : $model;
        $newopts['name'] = isset($opts['array']) && $opts['array'] ?
            "{$mname}[][{$field}]" :
            "{$mname}[{$field}]";
        //if (is_subclass_of($model, "\simp\Model"))
        if (is_object($model))
        {
            $newopts['value'] = $model->__get($field);
        }
        else
        {
            $newopts['value'] = '';
        }
    }
    return $newopts;
}

function GetURI()
{
  return $_SESSION['uri'];
}

function GetReturnURI()
{
  return $_SESSION['return_to'];
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
  return $flashstr;
}

function GetErrors()
{
  $errorar = $_SESSION['error'];
  unset($_SESSION['error']);
  if (is_array($errorar))
  {
    $errorstr = "<div id='error'><ul class='error'>\n";
    foreach($errorar as $field => $error)
    {
      $errorstr .= "  <li>$field: $error</li>\n";
    }
    $errorstr .= "</ul></div>";
  }
  return $errorstr;
}

function HasError($field)
{
  return (isset($_SESSION['error'][$field]));
}

function SetAuthorizedUser($id, $roles)
{
  $_SESSION['user'] = $id;
  $_SESSION['roles'] = array();
  foreach ($roles as $role)
  {
    array_push($_SESSION['roles'], $role->type);
  }
}

function ClearSession()
{
  $_SESSION['user'] = null;
  $_SESSION['roles'] = null;
}

function IsLoggedIn()
{
  return ($_SESSION['user'] != NULL);
}

function CheckRole($role)
{
    return (in_array($role, $_SESSION["roles"]));
}

function GetCurrentUser()
{
  return $_SESSION['user'];
}

function GetCurrentName()
{
  $usr = Doctrine_Query::create()
    ->select('u.login')
    ->from('User u')
    ->where('u.id = ?', GetCurrentUser())
    ->fetchOne();
  return $usr->first_name . " " . $usr->last_name . " (" . $usr->login . ")";
}

function GetCfgVar($name, $default = NULL)
{
  $var = \simp\DB::Instance()->FindOne("CfgVar", "name=?", array($name));

  if (!$var)
  {
    return $default;
  }
  return $var->value;
}

function SiteName()
{
    return GetCfgVar("site_name", "Leagueloo");
}

function GetBasePath()
{
  global $REL_PATH;
  return $REL_PATH;
}

function GetCSSPath($filename)
{
  return (GetBasePath() . "css/" . $filename);
}

function GetImagePath($filename)
{
  return (GetBasePath() . "images/" . $filename);
}

function GetJSPath($filename)
{
    return (GetBasePath() . "js/" . $filename);
}

function IncludeJS($filename)
{
    $js_include = '<script type="text/javascript" src="';
    $js_include .= GetJSPath($filename);
    $js_include .= '"></script>' . "\n";
    return $js_include;
}

