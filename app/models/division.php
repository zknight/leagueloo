<?php

class Division extends \simp\Model
{
    const TRAVEL = 1;
    const COMPETITIVE = 2;
    const INHOUSE = 3;
    const OTHER = 4;

    public static $levelopts = array(
        self::INHOUSE => "In-house recreational (U5-U12)",
        self::TRAVEL => "Travelling recreational (U12-U18)",
        self::COMPETITIVE => "Competitive (U11-U18)",
        self::OTHER => "Other (COASL, OPL)"
    );

    protected $_fields;
    protected $_games;
    public function Setup()
    {
        $this->_fields = array();
        $this->_games = array();
    }

    public function OnLoad()
    {
    }

    public function __get($property)
    {
        switch($property)
        {
        case "fields": 
            if (empty($this->_fields) && $this->id > 0)
            {
                $this->_fields = \simp\Model::Find("Field", "format = ?", array($this->format));
            /*
                $f_beans = \R::related($this->_bean, 'field');
                foreach ($f_beans as $id => $bean)
                {
                    $this->_fields[$id] = new Field($bean);
                }
             */
            }
            return $this->_fields;
            break;
        case "games":
            if (empty($this->_games) && $this->id > 0)
            {
                $this->_games = \simp\Model::Find("Game", "division_id = ? order by date asc", array($this->id));
            }
            return $this->_games;
            break;
        default:
            return parent::__get($property);
        }
    }

}
