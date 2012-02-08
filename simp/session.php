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
    //global $log;

    $session = Session::FindOne("Session", "sess_id =?", array($id));
    $new_session = true;
    if ($session)
    {
        //$log->logDebug("session exists");
        return $session->data;
    }
    else
    {
        //$log->logDebug("adding session $id");
        $session = Session::Create("Session");
        $session->sess_id = $id;
        $session->data = '';
        $session->touch = time();
        $session->Save();
    }
    return "";
}

function sess_write($id, $data)
{
    //global $log;
    //$log->logDebug("Attempthing to write $data to $id");
    $session = \simp\Model::FindOne("Session", "sess_id =?", array($id));
    $session->touch = time();
    $session->data = $data;
    $session->sess_id = $id;
    $session->Save();
  
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
    //global $log;
    ///// Changing to see if we can just delete all sessions that have expired instead of just this one

    $q = "delete from session where touch < ?";
    $max_session_min = GetCfgVar("max_session", 10);
    $max_session = $max_session_min * 60;
    $curtime = time();
    $exp_touch = $curtime - $max_session;
    \R::getAll($q, array($exp_touch));
    /* OLD WAY
    if ($_COOKIE["PHPSESSID"])
    {
        $id = $_COOKIE["PHPSESSID"];

        $session = Session::FindOne("Session", "sess_id =?", array($id));

        if ($session)
        {
            // TODO: make this a configurable variable
            $max_session_min = GetCfgVar("max_session", 10);
            $max_session = $max_session_min * 60;
            $curtime = time();
            //$log->logDebug("\$curtime = " . print_r($curtime, true));
            $delta = $curtime - $session->touch;
            //$log->logDebug("\$delta = $delta");
            if ($delta > $max_session)
            {
                //$log->logDebug("session timeout.  deleting $id");
                unset($_COOKIE["PHPSESSID"]);
                $session->Delete();
            }
        }
    }
     END OLD WAY*/
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
