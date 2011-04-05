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

    public function __construct()
    {
        global $APP_BASE_PATH;
        $this->_template_path = $APP_BASE_PATH . "/views/modules/";
        $this->_template_path .= SnakeCase(get_class($this));
    }

    /// Autoload function for loading modules
    public static function LoadModule($classname)
    {
        global $APP_BASE_PATH;
        $filename = SnakeCase($classname) . ".php";
        $path = $APP_BASE_PATH . "/modules/" . $filename;
        //$log->logDebug("attempting to find $classname @ $path");
        if (file_exists($path))
        {
            //echo("attempting to find $classname @ $path");
            require_once $path;
        }
    }

    public static function Load($name, $args=array())
    {
        $module = new $name;
        $module->Setup();
        $module->Render();
    }

    /// Override this function to provide logic for your Module
    protected function Setup()
    {
    }

    protected function Render()
    {
        //ob_start();
        require_once $this->_template_path . ".phtml";
        //$content = ob_get_contents();
        //ob_end_clean();
        //return $content;
    }
}
