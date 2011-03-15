<?
namespace app\admin;
class ConfigurationController extends \simp\Controller
{
    function Setup()
    {
        $this->AddAction('update', \simp\Request::PUT, 'Update');
    }

    function Index()
    {
        $this->site_name = $this->LoadVariable('site_name');
        $this->max_session = $this->LoadVariable('max_session');
        $this->site_email = $this->LoadVariable('site_email');
        $this->default_timezone = $this->LoadVariable('default_timezone');
        return true;
    }

    function Update()
    {
        $vars = $this->GetFormVariable('CfgVar');
        $cfg_var = \simp\DB::Instance()->Load('CfgVar', $this->GetParam(0));
        $cfg_var->UpdateFromArray($vars);
        \simp\DB::Instance()->Save($cfg_var);
        \Redirect(\Path::admin_configuration());
    }

    private function LoadVariable($name)
    {
        $var = \simp\DB::Instance()->FindOne("CfgVar", "name = ?", array($name));
        if (!$var)
        {
            $var = \simp\DB::Instance()->Create("CfgVar");
            $var->name = $name;
            $var->value = "[not set]";
            \simp\DB::Instance()->Save($var);
        }
        return $var;
    }
}
