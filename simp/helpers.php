<?
if (file_exists("app/helpers/master.php"))
  include ("app/helpers/master.php");

function h($str)
{
  return htmlentities($str, ENT_QUOTES);
}

function TextField($model, $field, $size = "20", $class = NULL, $id = NULL)
{

  $input_field = $field;
  if (isset($model))
  {
    $mname = get_class($model);
    $input_field = $mname . "[$field]";
    /*
    $err = $model->GetError($field);
    if (isset($err))
    {
      $class = 'error';
    }
     */
  }
  $html = "<input type='text' name='" . $input_field . "'";
  $html .= " size='" . $size . "'";
  if ($class != NULL) 
    $html .= " class='" . $class . "'";
  if ($id != NULL)
      $html .= " id='" . $id . "'";
  if (isset($model))
    $html .= " value='" . $model->__get($field) . "'";
  $html .= "/>";
  return $html; 
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
        $mname = get_class($model);
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

function l($text, $pathAr, $opts = array())
{
  global $REL_PATH;
  $pathstr = $REL_PATH;
  $class = '';
  $target = '';
  if (isset($pathAr['namespace']))
  {
    $path[] = $pathAr['namespace'];
  }
  if (isset($pathAr['controller']))
  {
    $path[] = $pathAr['controller'];
  }
  if (isset($pathAr['action']))
  {
    $path[] = $pathAr['action'];
  }
  if (isset($pathAr['id']))
  {
    $path[] = $pathAr['id'];
  }

  $pathstr .= implode($path, '/');
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

  $html = "<a href='$pathstr' $class $target>$text</a>";

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

function GetCurrentBusinessIdForUser()
{
  $usr = Doctrine_Query::create()
    ->select('u.business_id')
    ->from('User u')
    ->where('u.id = ?', GetCurrentUser())
    ->fetchOne();
  return $usr->business_id;
}

function GetCfgVar($name, $default = NULL)
{
  $var = Doctrine_Query::create()
    ->select('c.value')
    ->from('CfgVar c')
    ->where('c.name = ?', $name)
    ->fetchOne();

  if ($var == FALSE)
  {
    return $default;
  }
  return $var->value;
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
