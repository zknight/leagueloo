<?php
class Analytics extends \simp\Module
{

    public $account; 

    protected static function OnInstall()
    {
        self::SetAdminInterface(true);
    }

    protected function Setup($args)
    {
        $this->account = GetCfgVar("analytics_account", 0);
        $this->SetPermissions(
            array(
                "index" => Ability::ADMIN,
            )
        );
    }

    public function Index($method, $params, $vars)
    {
        if ($method == \simp\Request::PUT)
        {
            $this->account = $vars['Analytics']['account'];
            SetCfgVar("analytics_account", $this->account);
            AddFlash("\"Analytics\" account set to {$this->account}");
        }
        return true;
    }
}
