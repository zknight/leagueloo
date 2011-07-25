<?php
/// fields
///     short_title (auto)
///     title
///     copy
///     entity_type
///     entity_id
///     entity_name (auto)
///     link_location
class Page extends \simp\Model
{
    public $entity_name;

    // link locations
    const FRONT = 0; // entity main page
    const MAIN_MENU = 1;
    const LINK_MENU = 2;

    public static $locations = array(
        self::FRONT => "In list on category landing page (default)",
        self::MAIN_MENU => "On main menu for category",
        self::LINK_MENU => "In link menu for category"
    );

    public function Setup()
    {
        $this->SkipSanity("copy");
        $this->entity_name = "";
    }

    public function OnLoad()
    {
        if ($this->id > 0)
        {
            if ($this->entity_id == 0 && $this->entity_type == "Main") $this->entity_name = "Club";
            else
                $this->entity_name = \R::getCell(
                    "select name from " . SnakeCase($this->entity_type) . " where id = ?",
                    array($this->entity_id));
        }
    }

    public function BeforeSave()
    {

        $this->VerifyNotEmpty($this->title);
        $this->VerifyNotEmpty($this->copy);

        $arr = explode(" ", strtolower($this->title));
        $short_title = preg_replace("`[^a-zA-Z0-9_]`", "_", implode("_", $arr));
        $sz = strlen($short_title);
        $this->short_title = $sz > 31 ?
            substr($short_title, 0, 31) :
            $short_title;
        $this->entity_name = \R::getCell(
            "select name from " . SnakeCase($this->entity_type) . " where id = ?",
            array($this->entity_id));
        $this->updated_on = time();
        if ($this->id == 0) $this->created_on = $this->updated_on;

        return count($this->errors == 0);
    }

    public static function GetPublishedPages($user)
    {
        return self::GetPagesForUser($user, true);
    }

    public static function GetUnpublishedPages($user)
    {
        return self::GetPagesForUser($user, false);
    }

    public static function GetPagesForUser($user, $published)
    {
        $pages = array();
        if ($user->super)
        {
            $pages = self::Find('Page', 'published = ? order by entity_type, entity_id asc', array($published));
        }
        else
        {
            $abilities = self::Find(
                "Ability",
                "user_id = ? and level > ?",
                array("$user->id", Ability::EDIT));

            foreach ($abilities as $ability)
            {
                $pages = array_merge(
                    $pages,
                    self::Find(
                        'Page',
                        'entity_id = ? and entity_type = ? and published = ?',
                        array($ability->entity_id, $ability->entity_type, $published)));
            }
        }
        return $pages;
    }

    public function __get($property)
    {
        switch ($property)
        {
        case 'entity_designator':
            $entity_designator = "{$this->entity_type}:{$this->entity_id}";
            return $entity_designator;
            break;
        default:
            return parent::__get($property);
        }
    }

    public function __set($property, $value)
    {
        switch ($property)
        {
        case 'entity_designator':
            list($this->entity_type, $this->entity_id) = explode(":", $value);
            break;
        default:
            parent::__set($property, $value);
            break;
        }
    }

}
