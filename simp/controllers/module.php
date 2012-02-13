<?
namespace simp;
class ModuleController extends \simp\Controller
{
    function Setup()
    {
    }

    public function __call($name, $args)
    {
        // load module and delegate admin action
        $module = Module::Load(ClassCase($this->GetParam('module')));

        // check permissions for module
        if ($module->HasAccess($this->GetUser(), $name))
        {

            if ($module->$name($this->_method, $this->_params, $this->_form_vars) == true)
            {
                //$this->StoreLocation();
                $this->SetLayout($module->GetLayout());
                $this->content = $module->GetView($name);
                require_once $this->_layout_path . $this->_layout_name . ".phtml";
                return false;
            }
            else
            {
                //\Redirect(\Path::Home());
                return false;
            }
        }

        if ($this->UserLoggedIn()) 
        {
            AddFlash("You are not authorized to access this resource.");
            \Redirect(\Path::Home());
        }
        else
        {
            AddFlash("You must be logged in to access this module.");
            $this->StoreLocation();
            \Redirect(\Path::user_login());
        }
    }
}
