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
        $cfg_var = \simp\Model::FindById('CfgVar', $this->GetParam(0));
        $cfg_var->UpdateFromArray($vars);
        $cfg_var->Save();
        \Redirect(\Path::admin_configuration());
    }

}
