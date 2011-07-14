<?php
class UserMenu extends \simp\Module
{
    // types of things on user menu?
    // link to: be added to team (on team pages)
    // if on team, link to: upload photo, submit article
    // upload photo, submit article (for program)
    public function Setup($args)
    {
        $this->entity_type = GetEntityType();
        $this->entity_id = GetEntityId();
        $this->entity_name = GetEntityName();
        $this->user = 
    }
}
