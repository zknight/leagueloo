<?
namespace app;

class EventController extends \simp\Controller
{
    function Setup()
    {
    }

    function Calendar()
    {
        $this->StoreLocation();
        $this->entity_type = "Main";
        $this->entity_id = 0;
        $this->entity_name = "Club";

        $conditions = NULL;
        $values = array();
        if ($this->CheckParam("entity_type"))
        {
            $etype = ClassCase($this->GetParam("entity_type"));
            $this->entity_type = $etype;
            if ($etype == "Main")
            {
                $conditions = "entity_type <> ?";
                $values[] = 'Team';
            }
            else
            {
                $conditions = "entity_type = ?";
                $this->entity_name = $this->entity_type;
                $values[] = $etype;
                if ($this->CheckParam("id"))
                {
                    $eid = $this->GetParam("id");
                    $this->entity_id = $eid;
                    $conditions .= " and entity_id = ?";
                    $values[] = $eid;
                    $this->entity_name = \R::getCell(
                        "select name from " . SnakeCase($this->entity_type) . " where id = ?",
                        array($this->entity_id));
                }
            }
        }

        SetEntity($this->entity_type, $this->entity_id, $this->entity_name);
        $dt = new \DateTime("now");
        $today = FormatDateTime($dt->getTimestamp(), "d_m_Y");
        list($day, $month, $year) = explode("_", $today);
        if ($this->CheckParam("month") == true && $this->CheckParam("year") == true)
        {
            $m = $this->GetParam("month");
            $y = $this->GetParam("year");
            if ($m == $month || $y == $year)
            {
                $this->day = $day;
            }
            $month = $m; $year = $y;
        }
        global $log; $log->logDebug("Event::Calendar(): month=$month year=$year");
        $this->start_date = new \DateTime("$month/1/$year");

        $this->dates = \EventInfo::GetCalendarPeriod(
            $this->start_date,
            $conditions,
            $values
        );

        return true;
    }

    function Show()
    {
        $this->event_info = \simp\Model::FindById("EventInfo", $this->GetParam('id'));
        return true;
    }
}

