<?php
class Staff extends \simp\Model
{
    static public $staff_types = array(
        0 => 'Executive Board',
        1 => 'General Board',
        2 => 'Staff'
    );

    static public $position_types = array();
    static public $positions = array();

    public $file_info;
    public $rel_path;
    public $abs_path;
    public $img_path;

    public function Setup()
    {
        $this->file_info = NULL;
        $this->img_path = NULL;
        global $REL_PATH;
        global $BASE_PATH;
        $path = "resources/files/img/";
        $this->rel_path = $REL_PATH . $path;
        $this->abs_path = $BASE_PATH . $path;
        $this->SkipSanity('bio');
    }

    public function __get($property)
    {
        switch($property)
        {
        case "position":
            return "{$this->pos_type_idx}:{$this->pos_idx}";
            break;
        default:
            return parent::__get($property);
        }
    }

    public function __set($property, $value)
    {
        switch($property)
        {
        case "position":
            list($this->pos_type_idx, $this->pos_idx) = explode(":", $value);
            self::FetchPositions();
            $this->weight = self::$positions[$this->pos_type_idx][$this->pos_idx]['weight'];
            $this->group_weight = self::$position_types[$this->pos_type_idx]['weight'];
            $this->title = self::$positions[$this->pos_type_idx][$this->pos_idx]['name'];
            $this->group = self::$position_types[$this->pos_type_idx]['name'];
            break;
        default:
            parent::__set($property, $value);
            break;
        }
    }

    public function OnLoad()
    {
        $this->img_path = $this->rel_path . "staff_pics/{$this->image}";
    }

    public function BeforeSave()
    {
        $errors = 0;

        if (!$this->VerifyNotEmpty('first_name')) $errors++;
        if (!$this->VerifyNotEmpty('last_name')) $errors++;
        if (!$this->VerifyNotEmpty('title')) $errors++;

        if ($errors == 0)
        {
            $img_path = $this->abs_path . "staff_pics/";
            $info = $this->file_info;
            $img_name = NULL;
            $err = ProcessImage(
                $info,
                $img_path,
                $img_name,
                array('max_width' => 100)
            );

            if ($img_name != "") $this->image = $img_name;

            if ($err != false)
            { 
                $errors++;
                $this->SetError("image", $err);
            }
        }

        return $errors == 0;
    }

    public static function PositionTypes()
    {
        if (count(self::$position_types) == 0)
            self::FetchPositionTypes();
        //echo "returning:";
        //print_r (self::$position_types);
        return self::$position_types;
    }

    public static function Positions()
    {
        if (count(self::$positions) == 0)
            self::FetchPositions();
        return self::$positions; 
    }

    public static function AddPositionType($weight, $name)
    {
        if (count(self::$position_types) == 0)
            self::FetchPositionTypes();

        self::$position_types[] = array('name' => $name, 'weight' => $weight);
    }

    public static function AddPosition($pidx, $weight, $name)
    {
        echo "adding position $pidx $weight $name\n";
        if (count(self::$positions) == 0)
            self::FetchPositions();

        if (!is_array(self::$positions[$pidx])) self::$positions[$pidx] = array();

        self::$positions[$pidx][] = array('name' => $name, 'weight' => $weight);
    }

    public static function UpdatePositionType($idx, $weight, $name)
    {
        if (count(self::$position_types) == 0)
            self::FetchPositionTypes();

        self::$position_types[$idx]['name'] = $name;
        self::$position_types[$idx]['weight'] = $weight;
    }

    public static function UpdatePosition($pidx, $idx, $weight, $name)
    {
        if (count(self::$positions) == 0)
            self::FetchPositions();

        self::$positions[$pidx][$idx]['name'] = $name;
        self::$positions[$pidx][$idx]['weight'] = $weight;
    }

    public static function SavePositionTypes()
    {
        //echo "Saving...";
        //print_r(self::$position_types);
        SetCfgVar("staff_pos_types", serialize(self::$position_types));
    }

    public static function SavePositions()
    {
        SetCfgVar("staff_pos", serialize(self::$positions));
    }

    protected static function FetchPositionTypes()
    {
        $var = GetCfgVar("staff_pos_types", serialize(array()));
        //echo "fetched: ";
        //print_r($var);
        self::$position_types = unserialize($var);
        //print_r(self::$position_types);
    }

    protected static function FetchPositions()
    {
        if (count(self::$positions) == 0)
        {
            //echo "fetching positions...";
            $pos = array();
            if (count(self::$position_types) == 0) self::FetchPositionTypes();
            foreach (self::$position_types as $idx => $type)
            {
                $pos[$idx] = array();
            }

            $var = GetCfgVar("staff_pos", serialize($pos));
            self::$positions = unserialize($var);
        }

    }

    public static function DoneConfiguring($ok)
    {
        SetCfgVar("staff_cfg_ok", $ok);
    }

    public static function IsConfigured()
    {
        return GetCfgVar("staff_cfg_ok", false);
    }

    public static function StaffPositionOpts()
    {
        self::FetchPositionTypes();
        self::FetchPositions();

        $opts = array();
        foreach (self::$positions as $pidx => $pos)
        {
            $group = self::$position_types[$pidx]['name'];
            if (!isset($opts[$group])) $opts[$group] = array();
            foreach ($pos as $idx => $p)
            {
                $opts[$group]["$pidx:$idx"] = self::$positions[$pidx][$idx]['name'];
            }
        }

        return $opts;
    }
}
