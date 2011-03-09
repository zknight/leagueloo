<?
namespace simp;
require_once "utils.php";
/// Router takes a request object and routes it appropriately.
///
/// Routing involves:
///    - Discovering routes (controllers) [constructor]
///    - Searching request for matching route
class Router
{
    private $_default_controller;
    private $_route_map;

    function __construct()
    {
        global $APP_BASE_PATH;

        $this->_default_controller = "main";
        $this->_GenerateMap($this->_route_map, $APP_BASE_PATH . "/controllers");
    }

    function GetController(&$request, &$controller, &$path)
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
                    echo "found controller $controller\n";
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
        echo "looking at $dir\n";
        $entries = scandir($dir);
        foreach ($entries as $entry)
        {
            $curpath = "{$dir}/{$entry}";
            if (!preg_match("/(\.\.)|(\.)$/", $entry))
            {
            
                if (is_dir($curpath))
                {
                    //echo "dir: $curpath";
                    $map[$entry] = array("type" => "module");
                    $this->_GenerateMap($map[$entry]['module'], $curpath);
                }
                else if (is_file($curpath) && preg_match("/\.php$/", $entry))
                {
                    $name = preg_replace("/\.php/", "", $entry);
                    //echo "file: $curpath\n";
                    $map[$name] = array("type" => "file", "file" => $curpath);
                }
            }
        }
    }

}

