<?
class Events extends \simp\Module
{
    
    protected $num_days;
    protected $max_events;
    protected $show_category;
    protected static function OnInstall()
    {
        self::SetAdminInterface(true);
    }

    public function Setup($args)
    {
        require_once "models/event_cfg.php";
        $this->SetPermissions(
            array(
                "index" => Ability::ADMIN,
            )
        );

        $this->num_days = $this->GetCfgVar('num_days', 30);
        $this->max_events = $this->GetCfgVar('num_events', 5);
        $this->show_category = true;
        $this->entity_type = NULL;
        $this->entity_id = NULL;

        $now = new \DateTime("now");
        $then = clone $now;
        $then->add(new \DateInterval("P{$this->num_days->value}D"));
        $conditions = NULL;
        $values = array();
        if (isset($args['entity_type'])) 
        {
            // load events for entity
            $conditions = "entity_type = ?";
            $values[] = $args['entity_type'];
            $this->entity_type = $args['entity_type'];
            if (isset($args['entity_id']))
            {
                $conditions .= " and entity_id = ?";
                $values[] = $args['entity_id'];
                $this->show_category = false;
                $this->entity_id = $args['entity_id'];
            }
        } 

        $events = \EventInfo::GetEvents($now, $then, $conditions, $values);
        ksort($events);
        $this->events = array_slice($events, 0, $this->max_events->value, true);
        //ksort($this->events);

    }

    public function Index($method, $params, $vars)
    {
        if ($method == \simp\Request::POST)
        {
            global $log;
            $log->logDebug("Events module: vars " . print_r($vars, true));
            $log->logDebug("Events module: num_days " . print_r($this->num_days, true));
            $this->num_days->value = $vars['EventCfg']['num_days']['value'];
            $this->num_days->Save();
            $this->max_events->value = $vars['EventCfg']['max_events']['value'];
            $this->max_events->Save();
            AddFlash("\"Events\" Configuration Updated.");
        }
        return true;
    }

    public function Update($method, $params, $vars)
    {
        \Redirect(\Path::module("events", "admin"));
    }

    protected function GetCfgVar($name, $default = NULL)
    {
        $var = \simp\Model::FindOne("EventCfg", "name = ?", array($name));
        if (!$var)
        {
            $var = \simp\Model::Create("EventCfg");
            $var->name = $name;
            $var->value = $default == NULL ? "[not set]" : $default;
            $var->Save();
        }
        return $var;
    }



}
