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
        $this->option_string = implode(", ", $this->options);
    }

    public function BeforeSave()
    {
        global $log;
        $log->logDebug("Datum::BeforeSave() option_string = {$this->option_string}");
        $cleaned = preg_replace('/,\s/', ',', $this->option_string);
        $log->logDebug("Datum::BeforeSave() cleaned = {$cleaned}");
        $this->options = explode(",", $cleaned);
        $log->logDebug("Datum::BeforeSave() options = " . print_r($this->options, true));
        $this->opts = serialize($this->options);
        return true;
    }
}
