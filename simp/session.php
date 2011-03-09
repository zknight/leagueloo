<?
/* session management
 * Use DB to store session
 *
 * Session variables:
 * - session id
 * - login id
 * - flash
 * - last page visited
 * - last impression
 * - misc data (serialized hash)
 */

function sess_open($path, $name)
{
  return true;
}

function sess_close()
{
  return true;
}

function sess_read($id)
{
  //echo "sess_read($id);\n";
  $db = DB::GetDB();
  $res = $db->Fetch("sessions", "data", array('sess_id' => $id));
  $new_session = true;
  if (is_array($res) && (count($res) > 0))
  {
    Puts("res is array\n");
    //print_r($res);
    $sess_str = $res[0]['data'];

    /*
     */
    $new_session = false;
  }
  //echo "sess_str:";
  //print_r($sess_str);

  if (!$new_session)
  {
    //echo "$id:sess_str = $sess_str\n";
    return $sess_str;
  }
  else
  {
      Puts("adding session $id");
    $db->AddRow(
      "sessions", 
      array(
        'sess_id' => $id, 
        'data' => '',
        'touch' => time())
    );
  }
  return "";
}

function sess_write($id, $data)
{
  Puts("sess_write($id, $data)\n");
  $db = DB::GetDB();
  $touch = time();
  $db->UpdateRow(
    "sessions", 
    array('data' => $data, 'touch' => $touch),
    array('sess_id' => $id)
  );
  
  return true;
}

function sess_destroy($id)
{
  //echo "sess_destroy($id)\n";
}

function sess_gc($maxlife)
{
  //echo "sess_gc($maxlife)\n";
  return true;
}

function check_current_session_timeout()
{
  if ($_COOKIE["PHPSESSID"])
  {
    $id = $_COOKIE["PHPSESSID"];

    $db = DB::GetDB();
    $res = $db->Fetch("sessions", "touch", array('sess_id' => $id));
    if (is_array($res))
    {
      Puts(print_r($res, true));
      $touch = $res[0]['touch'];
      $max_session_m = $db->Fetch("cfg_var", "value", array('name' => 'session_timeout'));
      Puts("\$max_session_m = " . print_r($max_session_m, true));
      $max_session = $max_session_m[0]['value'] * 60;
      if ($max_session == 0)
      {
        $max_session = MAX_SESSION;
      }
      Puts("\$max_session = $max_session");

      $curtime = time();
      Puts("\$curtime = " . print_r($curtime, true));
      $delta = $curtime - $touch;
      Puts("\$delta = $delta");
      if ($delta > $max_session)
      {
        Puts("session timeout.  deleting $id");
        unset($_COOKIE["PHPSESSID"]);
        $db->Delete("sessions", array('sess_id' => $id));
      }
    }
  }
}

session_set_save_handler(
  "sess_open",
  "sess_close",
  "sess_read",
  "sess_write",
  "sess_destroy",
  "sess_gc");

check_current_session_timeout();
session_start();
