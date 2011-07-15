<?
namespace simp;
require_once "request.php";
require_once "helpers.php";
global $BASE_PATH;
if (file_exists($BASE_PATH . "app/controllers/app_controller.php"))
{
    require_once $BASE_PATH . "app/controllers/app_controller.php";
}


/// Controller is responsible for launching a view
/// A controller has actions, which are methods.  Each action has an 
/// associated view.
///
/// The request Method is checked for each action and the appropriate
/// class method is called
class Controller
{

    protected $_params;
    protected $_action_map;
    protected $_view_path;
    protected $_layout_name;
    protected $_layout_path;
    protected $_content;
    protected $_method;
    protected $_method_str;
    protected $_default_action;
    protected $_action;
    protected $_form_vars;
    protected $_current_user;
    protected $_authorization_params;
    protected $_current_url;
    protected $_preactions;

    function __construct()
    {
        global $APP_BASE_PATH;
        global $log;
        $log->logDebug("controller: " . get_class($this));
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
        $this->_method_str = "GET";
        $this->_action_map = array();
        $this->_current_user = NULL;
        $this->_authorization_params = array();
        $this->_preactions = array();
        $this->Setup();
        // can override this
        $this->_default_action = "Index";
        $this->_content = "";
        $this->_current_url = "";
    }

    protected function Setup()
    {
    }

    protected function SetLayout($layout)
    {
        $this->_layout_name = $layout;
    }

    protected function MapAction(
        $action, 
        $funcname,
        $method)
    {
        if (!method_exists($this, $funcname)) die("Action function $funcname doesn't exist for :" . get_class($this));
        if (array_key_exists($action, $this->_action_map))
        {
            $this->_action_map[$action] = array();
        }
        $this->_action_map[$action][$method] = $funcname;
    }

    protected function AddPreaction($actions, $funcname, $args=array())
    {
        global $log;
        if (!is_array($actions))
        {
            $actions = explode(",", preg_replace("/\s/", '', $actions));
        }
        if (!is_array($args))
        {
            $args = explode(",", preg_replace("/\s/", '', $args));
        }
        foreach ($actions as $action)
        {
            if (!isset($this->_preactions[$action])) $this->_preactions[$action] = array();
            $log->logDebug("AddPreaction: adding $funcname to $action");
            $this->_preactions[$action][] = array('function' => $funcname, 'args' => $args);
        }
        $log->logDebug("AddPreaction: preactions = " . print_r($this->_preactions, true));
    }

    protected function RequireAuthorization(
        $actions, 
        $entity_type = "", 
        $entity_id = 0, 
        $level = -1)
    {
        $ability = array(
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'level' => $level);

        if (is_array($actions))
        {
            foreach ($actions as $action)
            {
                $this->_authorization_params[$action] = $ability;
            }
        }
        else
        {
            global $log;
            $log->logDebug("adding authorization requirement for $actions");
            $this->_authorization_params[$actions] = $ability;
        }
    }

    protected function GetFormVariable($name)
    {
        if (array_key_exists($name, $this->_form_vars))
        {
            return $this->_form_vars[$name];
        }
        return null;
    }

    protected function CheckParam($key)
    {
        return array_key_exists($key, $this->_params);
    }

    protected function GetParam($index)
    {
        if (isset($this->_params[$index]))
            return $this->_params[$index];
        else
            return false;
    }

    protected function SetParam($index, $value)
    {
        $this->_params[$index] = $value;
    }

    public function SetAction($action)
    {
        $this->_action = $action;
    }

    public function GetAction()
    {
        return $this->_action;
    }

    function CallAction($action)
    {
        global $log;
        $this->_action = $action;
        $func = ClassCase($action);
        $save = true;
        $prefuncs = array();
        $log->logDebug("CallAction: looking up $action in map: \n" . print_r($this->_action_map, true));
        if (array_key_exists($action, $this->_action_map))
        {
            $log->logDebug("CallAction: looking up method {$this->_method}");
            if (array_key_exists($this->_method, $this->_action_map[$action]))
            {
                //$prefuncs = $this->_preactions[$action];
                $func = $this->_action_map[$action][$this->_method];
            }
        }

        if (isset($this->_preactions['all'])) $prefuncs = $this->_preactions['all'];
        if (isset($this->_preactions[$action])) $prefuncs = array_merge($prefuncs, $this->_preactions[$action]);
        foreach ($prefuncs as $prefunc)
        {
            $log->logDebug("CallAction: calling preaction {$prefunc['function']}");
            call_user_func_array(array($this, $prefunc['function']), $prefunc['args']);
        }
            
        $log->logDebug("CallAction: calling $func");
        $render = call_user_func(array($this, $func));
        if ($render)
        {
            $this->Render($this->_action);
            /* this ain't working
            if ($this->_method == Request::GET)
            {
                CheckHistory($this->_current_url);
            }
             */
        }
    }

    function Dispatch($request)
    {
        global $log;
        $this->_params = $request->GetParams();
        $this->_method = $request->GetMethod();
        $this->_method_str = $request->GetMethodStr();
        $this->_current_url = $request->GetRequestURL();

        $action = $request->GetAction();//ClassCase($request->GetAction());

        if (IsLoggedIn())
        {
            $this->_current_user = CurrentUser();
        }

        if ($this->Authorized($action))
        {
            $log->logDebug("Dispatching {$action} with request:\n " . print_r($request->GetRequest(), true));
            $log->logDebug("                      and params:\n " . print_r($this->_params, true));
            $this->_form_vars = $request->GetVariables();
            $this->CallAction($action);
        }
        else if (IsLoggedIn())
        {
            AddFlash("You are not authorized to access this resource.");
            $this->StoreLocation();
            \Redirect(GetReturnURL());
        }
        else
        {
            AddFlash("You must be logged in to access this resource.");
            $this->StoreLocation();
            \Redirect(\Path::user_login());
        }
    }

    function Render($view)
    {
        ob_start();
        require_once $this->_view_path . SnakeCase($view) . ".phtml";
        $this->content .= ob_get_contents();
        ClearRedirect();
        ob_end_clean();
        if (isset($this->_params['format']) && $this->_params['format'] == "no_template")
            echo $this->content;
        else
            require_once $this->_layout_path . $this->_layout_name . ".phtml";
    }

    function NotFound($msg = "")
    {
        global $BASE_PATH;
        $this->message_404 = $msg;
        ob_start();
        require_once $BASE_PATH . "/public/error404.phtml";
        $this->content .= ob_get_contents();
        ob_end_clean();
        require_once $this->_layout_path . $this->_layout_name . ".phtml";
    }

    protected function StoreLocation()
    {
        if ($this->_method == Request::GET) SetReturnURL($this->_current_url);
    }

    protected function UserLoggedIn()
    {
        return $this->_current_user != NULL;
    }

    protected function GetUser()
    {
        return $this->_current_user;
    }

    function Authorized($action)
    {
        $authorized = false;
        global $log;
        $log->logDebug("Checking authrorization for $action");
        if (array_key_exists($action, $this->_authorization_params))
        {
            if ($this->UserLoggedIn())
            {
                /*
                if ($this->_current_user->super ||
                    $this->_current_user->CanAccess(
                        $this->_authorization_params['entity_type'],
                        $this->_authorization_params['entity_id'],
                        $this->_authorization_params['level'])
                    )
                {
                    $authorized = true;
                }
                 */
                $authorized = true;
            }
        }
        else
        {
            $log->logDebug("no protection on this action.");
            $authorized = true;
        }
        return $authorized;
    }

    protected function LoadVariable($name, $default = NULL)
    {
        $var = \simp\Model::FindOne("CfgVar", "name = ?", array($name));
        if (!$var)
        {
            $var = \simp\Model::Create("CfgVar");
            $var->name = $name;
            $var->value = $default == NULL ? "[not set]" : $default;
            $var->Save();
        }
        return $var;
    }
}
?>
