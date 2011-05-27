<?
class Events extends \simp\Module
{
    protected static function OnInstall()
    {
        self::SetAdminInterface(true);
    }

    public function Setup($args)
    {
        // TODO: make this configurable through admin interface for event module
        $now = new \DateTime("now");
        $then = clone $now;
        $then->add(new \DateInterval("P1M"));
        $conditions = NULL;
        $values = array();
        if (isset($args['entity_id']) && isset($args['entity_type']))
        {
            // load events for entity
            $conditions = "entity_id = ? and entity_type = ?";
            $values[] = $args['entity_id'];
            $values[] = $args['entity_type'];
        }
        else
        {
            // load upcoming events
        }
        $this->events = \EventInfo::GetEvents($now, $then, $conditions, $values);
        ksort($this->events);
    }
}
