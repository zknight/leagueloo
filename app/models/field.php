<?php
class Field extends \simp\Model
{
    const THREEVTHREE = 1;
    const FOURVFOUR = 2;
    const SIXVSIX = 3;
    const EIGHTVEIGHT = 4;
    const ELEVENVELEVEN = 5;

    public static $formats = array(
        THREEVTHREE => '3 v 3 (U5, U6)',
        FOURVFOUR => '4 v 4 (U7, U8)',
        SIXVSIX => '6 v 6 (U9, U10)',
        EIGHTVEIGHT => '8 v 8 (Academy, U11, U12rec)',
        ELEVENVELEVEN => '11 v 11 (Academy, U12comp, U13-U18)',
    );

    protected $_complex;
    protected $_blackouts;

    public function Setup()
    {
        //$open = array();
        //$close = array();
        $this->_complex = FALSE;
        $this->_blackouts = array();
    }

    public function OnLoad()
    {
    }

    public function __get($property)
    {
        switch($property)
        {
        case 'complex':
            if (!$this->_complex && $this->id > 0)
            {
                $this->_complex = \simp\Model::FindById("Complex", $this->complex_id);
            }
            return $this->_complex;
        case 'blackouts':
            if (empty($this->_blackouts) && $this->id > 0)
            {
                $this->_blackouts = \simp\Model::Find("Blackout", "field_id = ?", array($this->id));
            }
            return $this->_blackouts;
        default:
            return parent::__get($property);
        }
    }

    public function BeforeSave()
    {
        return $this->HasErrors() == false;
    }

}
