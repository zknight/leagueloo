<?
class Debug
{
  private $content;
  function __construct()
  {
    $this->content = "<div class='debug'>\n<h2>debug output</h2>";
    $this->content .= "\n<pre>\n";
  } 

  function Puts($string)
  {
    global $DEBUG;
    if ($DEBUG)
    {
      $this->content .= $string . "\n";
    }
  }

  function GetContent()
  {
    $str = "$this->content\n</pre>\n</div>\n";
    return $str;
  }
}

$dbg = new Debug();
function Puts($str)
{
  global $dbg;
  if (isset($dbg))
    $dbg->Puts($str);
}

function GetDebug()
{
  global $dbg;
  return $dbg->GetContent();
}

?>
