<?
class FieldConditions extends \simp\Module
{
    public $fields;

    public function Setup()
    {
        $this->fields = \simp\Model::FindAll("Field");
    }

    public function Status($field)
    {
        $class = ($field->status == 0) ? 'open' : 'closed';
        return "<span class=\"$class\">{$field->status_text}</span>";
    }

    public function Time($field)
    {
        $time = FormatDateTime($field->updated_on, "Y/m/d H:i:s");
        return "<span class=\"time\">$time</span>";
    }
        
}
