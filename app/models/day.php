<?
class Day extends \simp\Model
{

    public function Setup()
    {
        $this->ManyToMany("Event");
    }
}
