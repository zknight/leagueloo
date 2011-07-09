<?
namespace simp;

/// A Module is a pluggable (from view) class that is responsible for 
/// rendering some piece of a view.  A module is independent from the
/// controller/action(view) that is currently being processed.
///
/// Modules can be loaded from a layout template, a view template, or 
/// another module template.
///
/// A Module has an associated template but is not rendered within
/// a layout template (as actions are).
///
/// Render a module by calling: 
/// \simp\Module::LoadModule('<module class name>')
///
class Module
{
    static protected $_has_admin = false;
    protected $_template_path;
    protected $_abilities;
    protected $_layout;
    public $id;

    public function __construct()
    {
        global $APP_BASE_PATH;
        $this->_layout = "admin";
        $this->_template_path = $APP_BASE_PATH . "/modules/";
        $this->_template_path .= SnakeCase(get_class($this));
        $this->_template_path .= "/views/";
    }

    /// Autoload function for loading modules
    public static function LoadModule($classname)
    {
        global $APP_BASE_PATH;
        $module_path = $APP_BASE_PATH . "/modules/" . SnakeCase($classname);
        $filename = $module_path . "/module.php";
        global $log; 
        $log->logDebug("attempting to find $classname @ $filename");
        if (file_exists($filename))
        {
            //echo("attempting to find $classname @ $path");
            require_once $filename;
        }
    }

    public static function Show($name, $args=array())
    {
        $module = Module::Load($name, $args);
        if ($module != null)
        {
            $module->Render();
        }
    }

    public static function Load($name, $args=array())
    {
        $plug_in = \simp\Model::FindOne("PlugIn", "name = ?", array(SnakeCase($name)));
        if ($plug_in->id > 0 && $plug_in->enabled == true)
        {
            $module = new $name;
            $module->id = $plug_in->id;
            $module->Setup($args);
            return $module;
        }
        return null;
    }


    /// Override this function to provide logic for your Module
    protected function Setup($args)
    {
    }

    protected function Render()
    {
        //ob_start();
        require_once $this->_template_path . "template.phtml";
        //$content = ob_get_contents();
        //ob_end_clean();
        //return $content;
    }

    public function GetView($view)
    {
        ob_start();
        require_once $this->_template_path . SnakeCase($view) . ".phtml";
        $content .= ob_get_contents();
        ob_end_clean();
        return $content;
    }

    protected function SetPermissions($ability_array)
    {
        $this->abilities = $ability_array;
    }

    protected static function SetAdminInterface($has_admin)
    {
        static::$_has_admin = $has_admin;
    }

    public static function HasAdmin()
    {
        return static::$_has_admin;
    }

    public function HasAccess($user, $view)
    {
        global $log;
        $view = SnakeCase($view);
        if (array_key_exists($view, $this->abilities))
        {
            if (isset($user))
            {
                $log->logDebug("checking $view for {$user->login}");
                $level = $this->abilities[$view];
                $log->logDebug("    level: $level");
                return $user->CanAccess("plug_in", $this->id, $level);
            }
            return false;
        }
        return true;
    }

    public static function Install()
    {
        static::OnInstall();
        $plug_in = \simp\Model::Create('PlugIn');
        $plug_in->name = SnakeCase(get_called_class());
        $plug_in->enabled = false;
        $plug_in->has_admin = static::HasAdmin();
        return $plug_in->Save();
    }

    // override to have installation specific stuff
    protected static function OnInstall()
    {
    }

    public function SetLayout($layout)
    {
        $this->_layout = $layout;
    }

    public function GetLayout()
    {
        return $this->_layout;
    }

}
