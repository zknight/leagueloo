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
    global $log;
  //echo "sess_read($id);\n";
    $db = \simp\DB::Instance();
  //$res = $db->Fetch("sessions", "data", array('sess_id' => $id));

    $session = $db->FindOne("Session", "sess_id =?", array($id));
    $new_session = true;
    if ($session)
    {
        $log->logDebug("res is array\n");
    //print_r($res);
    //$sess_str = $session->sess_str;

    /*
     */
    $new_session = false;
  }
  //echo "sess_str:";
  //print_r($sess_str);

    if (!$new_session)
    {
    //echo "$id:sess_str = $sess_str\n";
        return $session->sess_str;
    }
    else
    {
        $log->logDebug("adding session $id");
        $session = $db->Create("Session");
        $session->sess_id = $id;
        $session->data = '';
        $session->touch = time();
        $db->Save($session);
    }
    return "";
}

function sess_write($id, $data)
{
    $db = \simp\DB::Instance();
    $session = $db->FindOne("Session", "sess_id =?", array($id));
    $session->touch = time();
    $session->data = $data;
    $session->sess_id = $id;
    $db->Save($session);
  
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
    global $log;
    if ($_COOKIE["PHPSESSID"])
    {
        $id = $_COOKIE["PHPSESSID"];

        $db = \simp\DB::Instance();
        $session = $db->FindOne("Session", "sess_id =?", array($id));

        if ($session)
        {
            // TODO: make this a configurable variable
            $max_session_min = 60;
            $max_session = $max_session_min * 60;
            $curtime = time();
            $log->logDebug("\$curtime = " . print_r($curtime, true));
            $delta = $curtime - $session->touch;
            $log->logDebug("\$delta = $delta");
            if ($delta > $max_session)
            {
                $log->logDebug("session timeout.  deleting $id");
                unset($_COOKIE["PHPSESSID"]);
                $db->Delete($session);
            }
        }
    }
    /*
    $res = $db->Fetch("sessions", "touch", array('sess_id' => $id));
    if (is_array($res))
    {
      $log->logDebug(print_r($res, true));
      $touch = $res[0]['touch'];
      $max_session_m = $db->Fetch("cfg_var", "value", array('name' => 'session_timeout'));
      $log->logDebug("\$max_session_m = " . print_r($max_session_m, true));
      $max_session = $max_session_m[0]['value'] * 60;
      if ($max_session == 0)
      {
        $max_session = MAX_SESSION;
      }
      $log->logDebug("\$max_session = $max_session");

      $curtime = time();
      $log->logDebug("\$curtime = " . print_r($curtime, true));
      $delta = $curtime - $touch;
      $log->logDebug("\$delta = $delta");
      if ($delta > $max_session)
      {
        unset($_COOKIE["PHPSESSID"]);
        $db->Delete("sessions", array('sess_id' => $id));
      }
    }
  }
     */
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
