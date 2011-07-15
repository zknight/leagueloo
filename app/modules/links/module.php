<?php
class Links extends \simp\Module
{
    public function Setup($args)
    {
        $this->entity_type = GetEntityType();
        $this->entity_id = GetEntityId();
        $this->entity_name = GetEntityName();

        $this->links = \simp\Model::Find(
            "Link",
            "entity_type = ? and entity_id = ?",
            array(SnakeCase($this->entity_type), $this->entity_id)
        );
    }
}
