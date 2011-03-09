<?
namespace simp;
require_once "request.php";

class Controller
{

    protected $_router;
    protected $_params;
    protected $_action_map;
    protected $_view_path;
    protected $_layout_name;
    protected $_layout_path;
    protected $_content;
    protected $_method;
    protected $_default_action;

    function __construct($router)
    {
        global $APP_BASE_PATH;
        $this->_router =& $router;
        echo "<pre>class: " . get_class($this) . "</pre>\n";
        $class = explode("\\", 
            preg_replace("/app\\\/", "", get_class($this))
        );
        echo "<pre>elements: "; 
        print_r($class);
        echo "</pre>\n";
        $last_index = count($class) - 1;
        echo "class name: {$class[$last_index]}\n";
        $view_dir = SnakeCase(preg_replace("/Controller/", "/", $class[$last_index]));
        $this->_view_path = 
            $APP_BASE_PATH . 
            "/views/" . 
            implode("/", array_slice($class, 0, $last_index-1)) .
            $view_dir;
        echo "<pre>view_dir: " . $view_dir . " view_path: " . $this->_view_path. "\n</pre>";
        $this->_layout_path = $APP_BASE_PATH . "/views/layouts/";
        $this->_layout_name = "default";
        $this->_method = Request::GET;
        $this->_action_map = array();
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


    function Dispatch($request)
    {
        global $log;
        echo "Dispatching.\n";
        $path = '';
        $controller_name = '';
        $request_array = &$request->GetRequest();

        $this->_method =  $request->GetMethod();

        if ((count($request_array) < 1) && method_exists($this, $this->_default_action))
        {
            $this->CallAction($this->_default_action);
            $log->logDebug("calling default action");
        }
        // check for controller match
        else if ($this->_router->GetController($request_array, $controller_name, $path))
        {
            // load controller and dispatch request
            require_once($path);
            $controller = new $controller_name($this->_router);
            $log->logDebug("dispatching $controller_name");
            $controller->Dispatch($request);
        }

        // check for action match
        else if($action = $this->_action_map[$request_array[0]][$this->_method])
        {
            // Action exists.  TODO: Call "before" filter 
            // and change this to call the mapped action
            array_shift($request_array);
            $log->logDebug("calling action $action with request array " . print_r($request_array, true));
            $this->CallAction($action, $request_array);
        }
        // check for delegated controller
        else if ($delegate = $this->Delegate($request_array[0]))
        {
            //array_shift($request_array);
            array_unshift($request_array, $delegate['action']);
            array_unshift($request_array, $delegate['controller']);
            echo "<pre>delegated request array:\n";
            print_r($request_array);
            echo "</pre>\n";
            if ($this->_router->GetController($request_array, $controller_name, $path))
            {
                // load controller and dispatch request
                require_once($path);
                $controller = new $controller_name($this->_router);
                $log->logDebug("dispatching delegate $controller_name");
                $controller->Dispatch($request);
            }
            else
            {
                //$this->Render("error404");
            }

        }
        // 404 cuz nuffin found
        else
        {
            //$this->Render("error404");
        }
    }

    function Render($view)
    {
        ob_start();
        require_once $this->_view_path . SnakeCase($view) . ".phtml";
        $this->content .= ob_get_contents();
        ob_end_clean();
        require_once $this->_layout_path . $this->_layout_name . ".phtml";
    }

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
}
?>
