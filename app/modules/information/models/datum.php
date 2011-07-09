<?php
class Datum extends \simp\Model
{
    // fields
    // - type
    // - required
    // - max_chars

    public $options;
    public $option_string;
    public static $types = array(
        "check box",
        "text box",
        "selection"
    );

    public function Setup()
    {
        $this->options = array();
    }

    public function OnLoad()
    {
        if ($this->id > 0) $this->options = unserialize($this->opts);
        $this->option_string = implode("\n", $this->options);
    }

    public function AfterUpdate()
    {
        $this->options = explode("\n", $this->option_string);
        $this->opts = serialize($this->options);
    }
}
