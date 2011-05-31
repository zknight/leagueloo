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
        $this->event_data = array();
        $conditions = NULL;
        $values = array();
        if ($this->CheckParam("entity_type"))
        {
            $etype = ClassCase($this->GetParam("entity_type"));
            $this->event_data['entity_type'] = $etype;
            $conditions = "entity_type = ?";
            $values[] = $etype;
            if ($this->CheckParam("id"))
            {
                $eid = $this->GetParam("id");
                $this->event_data['entity_id'] = $eid;
                $conditions .= " and entity_id = ?";
                $values[] = $eid;
            }
        }

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

