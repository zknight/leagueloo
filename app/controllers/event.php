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
        $etype = ClassCase($this->GetParam("entity_type"));
        $eid = $this->GetParam("id");
        $this->event_data = array('entity_type' => $etype, 'entity_id' => $eid);
        $dt = new \DateTime("now");
        $today = FormatDateTime($dt->getTimestamp(), "d_m_Y");
        list($day, $month, $year) = explode("_", $today);
        $this->start_date = new \DateTime("$month/1/$year");
        $this->dates = \EventInfo::GetCalendarPeriod(
            $this->start_date,
            "entity_type = ? and entity_id = ?",
            array($etype, $eid)
        );

        return true;
    }

    function Show()
    {
        $this->StoreLocation();
    }
}

