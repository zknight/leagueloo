<?
namespace simp;
require_once "utils.php";
require_once "breadcrumb.php";
/// Router takes a request object and routes it appropriately.
///
/// Routing involves:
///    - Discovering routes (controllers) [constructor]
///    - Searching request for matching route
class Router
{
    private $_default_controller;
    private $_routes;
    private $_log;
    private $_params;

    function __construct()
    {
        //global $APP_BASE_PATH;
        global $log;

        $this->_log = &$log;

        /*
        $this->_default_controller = array("main");
        $this->_GenerateMap($this->_route_map, $APP_BASE_PATH . "/controllers");
        $log->logDebug("route map:\n" . print_r($this->_route_map, true));
         */
    }
    
    function AddRoute($method, $route, $controller, $action = "index", $params = array())
    {
        $this->_routes[] = array($method, $route, $controller, $action, $params);
    }

    function Put($uh)
    {
        //echo $uh;
    }

    function Route($request)
    {
        $this->Put( "<pre>");
        $this->_log->logDebug("Routing request: {$request->GetRequestURL()}");
        $uri = $request->GetRequestURL();
        $this->Put( "Request URL: $uri\n");
        $this->_params = $request->GetParams();

        foreach ($this->_routes as $handler)
        {
            list($method, $route_exp, $controller, $action, $route_params) = $handler;
            $this->_log->logDebug("checking $route_exp");

            // check method
            if ($request->GetMethod() !== $method)
            {
                // method doesn't match, look at next
                // route
                $this->Put( "method doesn't match\n");
                continue;
            } 

            // check for exact or global match
            if ($route_exp === $uri || $route_exp === '*')
            {
                $this->_log->logDebug("got exact or global");
                $match = true;
            }
            else  
            {
                $route = $substr = null;
                $i = 0;
                while (true) 
                {
                    //$this->Put( "route_exp[$i] = {$route_exp[$i]}\n";
                    if ($route_exp[$i] === '')
                    {
                        break;
                    }
                    elseif (null === $substr)
                    {
                        $c = $route_exp[$i];
                        $n = $route_exp[$i + 1];
                        if ($c === '[' || $c === '(' || $c === '.' ||
                            $n === '?' || $n === '+' || $n === '*' || $n === '{')
                        {
                            $substr = $route;
                        }
                    }
                    $route .= $route_exp[$i++];
                }
                if (null === $substr || strpos($uri, $substr) !== 0)
                {
                    $this->Put( "substr = $substr\n");
                    $this->Put( "uri = $uri\n");
                    continue;
                }
                
                $this->Put( "would compile: $route\n");
                $regex = $this->CompileRoute($route);

                $this->Put( "regex: $regex\n");
                $match = preg_match($regex, $uri, $params);
                $this->Put( "match: $match\n");
            }

            // here's where I'd check for negation if I wanted to include that functionality
            if ($null !== $params)
            {
                $this->_params = array_merge($this->_params, $params, $route_params);
            }

            if (true == $match)
            {
                if (!array_key_exists('action', $this->_params))
                {
                    $this->_params['action'] = $action;
                }
                $request->SetParams($this->_params);
                $this->Put( "would dispatch controller: $controller with params:\n");
                $this->Put(print_r($this->_params, true));
                // found a match, route it
                break;
            }
            // load controller and dispatch this request
        }

        if (false == $match)
        {
            // render 404
        }

        // set breadcrumb
        Breadcrumb::Instance()->SetFromRequest($request);

        $this->Put( "</pre>");

        // load controller and dispatch it

        global $APP_BASE_PATH;
        $path = $APP_BASE_PATH . "/controllers/{$controller}.php";
        require_once $path;
        $controller_name = "\\app\\" . $this->NamespacedName($controller) . "Controller";
        $controller = new $controller_name;
        $controller->Dispatch($request);
    }

    private function CompileRoute($route)
    {
        if (preg_match_all('`(/?\.?)\[([^:]*+)(?::([^:\]]++))?\](\?)?`', $route, $matches, PREG_SET_ORDER)) {
            $this->Put("CompileRoute matches:\n");
            $this->Put(print_r($matches, true));
            $this->Put( "\n");
            $match_types = array(
                'i'  => '[0-9]++',
                'a'  => '[0-9A-Za-z_]++',
                'h'  => '[0-9A-Fa-f]++',
                '*'  => '.+?',
                '**' => '.++',
                ''   => '[^/]++'
            );
            foreach ($matches as $match) {
                $this->Put("match: \n"); 
                $this->Put(print_r($match, true));
                list($block, $pre, $type, $param, $optional) = $match;

                if (isset($match_types[$type])) {
                    $type = $match_types[$type];
                }
                $pattern = '(?:' . ($pre !== '' && strpos($route, $block) !== 0 ? $pre : null)
                         . '(' . ($param !== '' ? "?<$param>" : null) . $type . '))'
                         . ($optional !== null ? '?' : null);
                $this->Put( "pattern: $pattern\n");

                $route = str_replace($block, $pattern, $route);
            }
            $route = ltrim($route, "/"); 
            $route = "/$route/?";
        }
        return "`^$route$`";
    }

    private function NamespacedName($name)
    {
        $name_elements = explode('/', $name);
        $name_elements = array_reverse($name_elements);
        $name_elements[0] = ClassCase($name_elements[0]);
        $name_elements = array_reverse($name_elements);
        return implode('\\', $name_elements);
    }

    private function GetController(&$request, &$controller, &$path)
    {
        $is_controller = false;
        $namespace = "\\app\\";

        // save request in case no controller matches
        $original_request = $request;

        // initialize map reference to top of tree
        $map =& $this->_route_map;

        $done = false;

        while (!$done)
        {
            // get next element of request to test
            $test = array_shift($request);
            $match = $map[$test];

            if ($match)
            {
                // check to see if this match is module
                // containing controller and, if so,
                // go to next level of tree to test next request element
                if ($match['type'] == 'module')
                {
                    $map =& $map[$test]['module'];
                    $namespace .= "$test\\";
                }
                else if ($match['type'] == 'file')
                {
                    $path = $match['file'];
                    $done = true;
                    $is_controller = true;
                    $controller = "$namespace" . ClassCase($test) . "Controller";
                }
            }
            else $done = true;
        }

        // if a controller wasn't found, reset request
        // so that caller can determine what to do with it
        if (!$is_controller) $request = $original_request;

        return $is_controller;
    }

    // TODO: make this cache (APC?) so that it only runs when the 
    // first request comes in after launching the application
    private function _GenerateMap(&$map, $dir)
    {
        $entries = scandir($dir);
        foreach ($entries as $entry)
        {
            $curpath = "{$dir}/{$entry}";
            if (!preg_match("/(\.\.)|(\.)$/", $entry))
            {
            
                if (is_dir($curpath))
                {
                    $map[$entry] = array("type" => "module");
                    $this->_GenerateMap($map[$entry]['module'], $curpath);
                }
                else if (is_file($curpath) && preg_match("/\.php$/", $entry))
                {
                    $name = preg_replace("/\.php/", "", $entry);
                    $map[$name] = array("type" => "file", "file" => $curpath);
                }
            }
        }
    }

}

