<?
class Notices extends \simp\Module
{
    public $notices;
    public $cycle_delay;

    protected static function OnInstall()
    {
        self::SetAdminInterface(true);
    }

    public function Setup($args)
    {
        require_once "models/notice.php";
        require_once "models/notice_cfg.php";
        $this->SetPermissions(
            array(
                "index" => Ability::ADMIN,
                "add" => Ability::ADMIN,
                "edit" => Ability::ADMIN,
                "delete" => Ability::ADMIN
            )
        );
        $dt = new \DateTime("now");
        $date = $dt->getTimestamp();
        $this->notices = \simp\Model::Find(
            "Notice", 
            "start_date <= ? and end_date >= ?",
            array($date, $date)
        );

        $this->cycle_delay = $this->GetCfgVar('cycle_delay', 15);
        $this->show_menu = IsLoggedIn() ? $this->HasAccess(CurrentUser(), 'add') : false;
    }

    public function Index($method, $params, $vars)
    {

        $this->all_notices = \simp\Model::FindAll("Notice");
        if ($method == \simp\Request::POST)
        {
            $this->cycle_delay->value = $vars['NoticeCfg']['value']['cycle_delay'];
            $this->cycle_delay->Save();
            AddFlash("\"Notice\" Configuration Updated.");
        }
        return true;
    }

    public function Add($method, $params, $vars)
    {
        $this->notice = \simp\Model::Create("Notice");
        if ($method == \simp\Request::POST)
        {
            $this->notice->UpdateFromArray($vars["Notice"]);
            if ($this->notice->Save())
            {
                AddFlash("Notice {$this->notice->name} created.");
                \Redirect(GetReturnURL());
            }
            //print_r($this->notice);
        }
        return true;
    }

    public function Edit($method, $params, $vars)
    {
        $this->notice = \simp\Model::FindById("Notice", $params['id']);
        if ($method == \simp\Request::PUT)
        {
            $this->notice->UpdateFromArray($vars['Notice']);
            if ($this->notice->Save())
            {
                AddFlash("Notice {$this->notice->name} updated.");
                \Redirect(GetReturnURL());
            }
        }
        return true;
    }

    public function Delete($method, $params)
    {
        if ($method == \simp\Request::DELETE)
        {
            $notice = \simp\Model::FindById("Notice", $params['id']);
            if ($notice->id > 0)
            {
                $name = $notice->name;
                $notice->Delete();
                AddFlash("Notice $name deleted.");
            }
            else
            {
                AddFlash("That notice is invalid.");
            }
        }
        \Redirect(GetReturnURL());
    }

    protected function GetCfgVar($name, $default = NULL)
    {
        $var = \simp\Model::FindOne("NoticeCfg", "name = ?", array($name));
        if (!$var)
        {
            $var = \simp\Model::Create("NoticeCfg");
            $var->name = $name;
            $var->value = $default == NULL ? "[not set]" : $default;
            $var->Save();
        }
        return $var;
    }
}
