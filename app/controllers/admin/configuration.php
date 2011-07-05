<?
namespace app\admin;
class ConfigurationController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            'index',
            'update'
        );

        $this->MapAction("update", "Update", \simp\Request::PUT);
    }

    function Index()
    {
        global $_SERVER;
        $this->site_name = $this->LoadVariable('site_name');
        $this->max_session = $this->LoadVariable('max_session');
        $this->site_email = $this->LoadVariable('site_email');
        $this->default_timezone = $this->LoadVariable('default_timezone');
        $this->site_address = $this->LoadVariable('site_address', $_SERVER['SERVER_NAME']);
        $this->max_recent = $this->LoadVariable('max_recent', 5);
        global $log;
        $log->logDebug("site_address from global: {$_SERVER['SERVER_NAME']}");
        return true;
    }

    function Update()
    {
        $vars = $this->GetFormVariable('CfgVar');
        $cfg_var = \simp\Model::FindById('CfgVar', $this->GetParam('id'));
        $cfg_var->UpdateFromArray($vars);
        $cfg_var->Save();
        \Redirect(\Path::admin_configuration());
    }

}
