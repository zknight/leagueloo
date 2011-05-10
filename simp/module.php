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
    protected $_template_path;
    protected $_abilities;
    public $id;

    public function __construct()
    {
        global $APP_BASE_PATH;
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
        $module = Module::Load($name);
        if ($module != null)
        {
            $module->Render();
        }
    }

    public static function Load($name)
    {
        $plug_in = \simp\Model::FindOne("PlugIn", "name = ?", array(SnakeCase($name)));
        if ($plug_in->id > 0 && $plug_in->enabled == true)
        {
            $module = new $name;
            $module->id = $plug_in->id;
            $module->Setup();
            return $module;
        }
        return null;
    }


    /// Override this function to provide logic for your Module
    protected function Setup()
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

    public function HasAccess($user, $view)
    {
        global $log;
        $view = SnakeCase($view);
        $log->logDebug("checking $view for {$user->login}");
        if (isset($user) and array_key_exists($view, $this->abilities))
        {
            $level = $this->abilities[$view];
            $log->logDebug("    level: $level");
            return $user->CanAccess("plug_in", $this->id, $level);
        }
        return false;
    }
}
