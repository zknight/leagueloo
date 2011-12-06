<?php
/// Link model
///
/// Provides an html link which is owned by an entity
/// fields:
///     uri: uri of target
///     new_window: whether target should load in new window (make default)
///     text: text of link
///     entity_type: program or team
///     entity_id: id of entity owner
///     entity_name: name of entity
///     weight: relative order
///     disabled
class Link extends \simp\Model
{
    public $entity_name;
    public $entity_designator;

    public function Setup()
    {
        $this->SkipSanity('href');
        $this->entity_name = "";
    }

    public function OnLoad()
    {
        if ($this->id > 0)
        {
            $this->entity_name = \R::getCell("select name from " . SnakeCase($this->entity_type) . " where id = ?",
                array($this->entity_id));
            $this->entity_designator = "{$this->entity_type}:{$this->entity_id}";
        }
    }

    public function BeforeSave()
    {
        $this->VerifyNotEmpty('uri');
        $this->VerifyNotEmpty('text');
        $this->VerifyMaxLength('text', 40);

        if (strpos($this->uri, 'http://') !== 0)
        {
            $this->uri = "http://" . $this->uri;
        }

        if ($this->entity_designator)
            list($this->entity_type, $this->entity_id) = explode(":", $this->entity_designator);

        return count($this->errors) == 0;
    }
}
