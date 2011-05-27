<?
namespace app\admin;
class PlugInController extends \simp\Controller
{
    function Setup()
    {
        $this->RequireAuthorization(
            array(
                'index',
                'install',
                'enable',
                'disable'
            )
        );

        //$this->MapAction("add", "Create", \simp\Request::POST);
        //$this->MapAction("edit", "Update", \simp\Request::PUT);
        //$this->MapAction("delete", "Remove", \simp\Request::DELETE);
    }

    function Index()
    {
        $this->uninstalled_plug_ins = array();
        $this->plug_ins = array();
        // find modules in app/modules
        global $APP_BASE_PATH;
        $module_dir = $APP_BASE_PATH . "/modules/";
        $entries = scandir($module_dir);
        foreach ($entries as $entry)
        {
            // check for module.php
            $curpath = "{$module_dir}/{$entry}";
            if (is_dir($curpath) && file_exists("{$curpath}/module.php"))
            {
                // check for module name in plug-ins
                global $log; $log->logDebug("Checking to see if $entry is installed.");
                $plug_in = \simp\Model::FindOne("PlugIn", "name = ?", array($entry));
                if ($plug_in->id > 0)
                {
                    $this->plug_ins[] = $plug_in;
                }
                else
                {
                    $this->uninstalled_plug_ins[] = $entry;
                }
            }
        }

        return true;
    }

    function Install()
    {
        $name = $this->GetParam('name');
        // check to see if plug-in has module admin interface
        $model_name = ClassCase($name);
        if ($model_name::Install())
        {
            AddFlash("Plug-in {$plug_in->name} installed.");
        }
        else
        {
            AddFlash("Unabled to install plug-in {$name}.");
        }
        \Redirect(\Path::admin("plug_in"));
    }

    function Enable()
    {
        $id = $this->GetParam('id');
        $plug_in = \simp\Model::FindById('PlugIn', $id);
        if ($plug_in->id > 0)
        {
            $plug_in->enabled = true;
            if ($plug_in->Save())
            {
                AddFlash("Plug-in " . HumanCase($plug_in->name) . " enabled.");
            }
            else
            {
                AddFlash("Failed to enable plug-in " . HumanCase($plug_in->name));
            }
        }
        else
        {
            AddFlash("Failed to find that plug-in.  Please contact system administrator.");
        }
        \Redirect(\Path::admin("plug_in"));
    }

    function Disable()
    {
        $id = $this->GetParam('id');
        $plug_in = \simp\Model::FindById('PlugIn', $id);
        if ($plug_in->id > 0)
        {
            $plug_in->enabled = false;
            if ($plug_in->Save())
            {
                AddFlash("Plug-in " . HumanCase($plug_in->name) . " disabled.");
            }
            else
            {
                AddFlash("Failed to disable plug-in " . HumanCase($plug_in->name));
            }
        }
        else
        {
            AddFlash("Failed to find that plug-in.  Please contact system administrator.");
        }
        \Redirect(\Path::admin("plug_in"));
    }

}
