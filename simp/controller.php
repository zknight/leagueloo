<?
namespace simp;
require_once "request.php";
require_once "helpers.php";

class Controller
{

    protected $_params;
    protected $_action_map;
    protected $_view_path;
    protected $_layout_name;
    protected $_layout_path;
    protected $_content;
    protected $_method;
    protected $_default_action;
    protected $_action;
    protected $_form_vars;
    protected $_current_user;

    function __construct()
    {
        global $APP_BASE_PATH;
        global $log;
        $log->logDebug("class: " . get_class($this));
        $class = explode("\\", 
            preg_replace("/app/", "", get_class($this))
        );
        $log->logDebug("elements: " . print_r($class, true));
        $last_index = count($class) - 1;
        $log->logDebug("class name: {$class[$last_index]}");
        $view_dir = SnakeCase(preg_replace("/Controller/", "/", $class[$last_index]));
        $this->_view_path = 
            $APP_BASE_PATH . 
            "/views" . 
            implode("/", array_slice($class, 0, $last_index)) . "/" .
            $view_dir;
        $log->logDebug("view_dir: " . $view_dir . " view_path: " . $this->_view_path); 
        $this->_layout_path = $APP_BASE_PATH . "/views/layouts/";
        $this->_layout_name = "default";
        $this->_method = Request::GET;
        $this->_action_map = array();
        $this->_current_user = NULL;
        $this->Setup();
        // can override this
        $this->_default_action = "Index";
        $this->_content = "";
    }

    function Setup()
    {
    }

    function AddAction($key, $method, $action)
    {
        if (!method_exists($this, $action)) die("$action doesn't exist for :" . get_class($this));
        if (!$this->_action_map[$key]) $this->_action_map[$key] = array();
        $this->_action_map[$key][$method] = $action;
    }

    function GetFormVariable($name)
    {
        return $this->_form_vars[$name];
    }

    function GetParam($index)
    {
        return $this->_params[$index];
    }

    /// override this to have your controller Delegate to another
    function Delegate($request)
    {
        return null;
    }

    function CallAction($action, $params = NULL)
    {
        global $log;
        $this->_params = $params;
        $render = call_user_func(array($this, $action));
        if ($render)
        {
            $this->Render($action);
        }
    }

    function CheckDefault($request)
    {
        global $log;
        $handled = false;
        if ((count($request->GetRequest()) < 1) && method_exists($this, $this->_default_action))
        {
            $handled = true;
            //$this->CallAction($this->_default_action);
            //$log->logDebug("calling default action");
            $this->_action = $this->_default_action;
        }
        return $handled;
    }

    function CheckAction(&$request)
    {
        global $log;
        $handled = false;
        $request_params = &$request->GetRequest();
        if ($action = $this->_action_map[$request_params[0]][$request->GetMethod()])
        {
            $this->_action = $action;
            array_shift($request->GetRequest());
            $handled = true;
        }
        return $handled;
    }

    function CanHandle($request)
    {
        $can_handle = $this->CheckAction($request);
        if (!$can_handle) $can_handle = $this->CheckDefault($request);
        return $can_handle;
    }

    function Dispatch($request)
    {
        global $log;
        $path = '';
        $controller_name = '';
        if (IsLoggedIn())
        {
            $this->_current_user = CurrentUser();
        }

        $log->logDebug("Dispatching {$this->_action} with request:\n " . print_r($request->GetRequest(), true));

        //$this->_method =  $request->GetMethod();
        $this->_form_vars = $request->GetVariables();
        $this->CallAction($this->_action, $request->GetRequest());
    }

    function Render($view)
    {
        global $REL_PATH;
        ob_start();
        require_once $this->_view_path . SnakeCase($view) . ".phtml";
        $this->content .= ob_get_contents();
        ob_end_clean();
        require_once $this->_layout_path . $this->_layout_name . ".phtml";
    }

    function UserLoggedIn()
    {
        return $this->_current_user != NULL;
    }

    function GetUser()
    {
        return $this->_current_user;
    }

    // TODO: change this!
    function Authorized($action)
    {
        $role = '';
        $require_auth = false;
        $authorized = true;
        if (isset($this->auth_roles[$action]))
        {
            $roles = explode(",", $this->auth_roles[$action]);
            $require_auth = true;
        }
        else if (isset($this->auth_roles["all"]))
        {
            $roles = explode(",", $this->auth_roles["all"]);
            $require_auth = true;
        }
        if ($require_auth)
        {
            Puts("requires auth role $role");
            $user_id = $_SESSION["user"];
        
            if (!isset($user_id)) //|| ($role != $_SESSION["role"]))
            {
                AddFlash("You must log in to access this page.");
                Redirect("login");
            }
            else //if (!in_array($role, $_SESSION["roles"]))
            {
                foreach($roles as $role)
                {
                    if (in_array($role, $_SESSION["roles"]))
                    {
                        $authorized = true;
                        break;
                    }
                    else
                    {
                        $authorized = false;
                    }
                }
            }
        }
        return $authorized;
    }

    protected function LoadVariable($name)
    {
        $var = \simp\Model::FindOne("CfgVar", "name = ?", array($name));
        if (!$var)
        {
            $var = \simp\Model::Create("CfgVar");
            $var->name = $name;
            $var->value = "[not set]";
            $var->Save();
        }
        return $var;
    }
}
?>
