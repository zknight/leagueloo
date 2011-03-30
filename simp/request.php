<?php
namespace simp;
/// Request encapsulates the parameters determined from the request received.
///
/// Parameters are: 
///     request_array - an array of made from the request URL relative to the application location
///     post_variables - an associative array of post variables (name/value)
///     method - the request method
///     base_path - the base path of the application with respect to server root
///     relative_path - the path relative to the application base
class Request 
{

    private $_request_array;
    private $_post_variables;
    private $_method;
    private $_base_path;
    private $_relative_path;
    private $_method_map;

    const GET = 0;
    const POST = 1;
    const DELETE = 2;
    const PUT = 3;

    function __construct() 
    {
        global $_SERVER;
        global $_SESSION;
        global $_POST;
        global $log;

        $this->_method_map = array("GET" => Request::GET, "POST" => Request::POST, "DELETE" => Request::DELETE, "PUT" => Request::PUT);
        $pathAr = array();
        // since this may be installed in a subdirectory 
        // of the DOCUMENT_ROOT, strip it off of the REQUEST_URI
        $this->_relative_path = preg_replace("/index.php/", "", $_SERVER["SCRIPT_NAME"]);
        $rel_path = preg_replace("/\//", "\/", trim($this->_relative_path, "/"));
        $log->logDebug("rel_path: $rel_path");
        $url = preg_replace("/$rel_path/", "", $_SERVER["REQUEST_URI"]);
        $log->logDebug("url: $url");
        $url = trim($url, "/");
        $log->logDebug("url: $url");
        $_SESSION["url"] = $url;
        if ($url != '') $this->_request_array = explode ("/", $url);
        else $this->_request_array = array();
        $text = print_r($this->_request_array, true);
        $log->logDebug("request_array = $text"); 
        $this->_base_path = preg_replace("/index.php/", "", $_SERVER["SCRIPT_FILENAME"]);
        $this->_DetermineMethod();
        $this->_post_variables = $_POST;

    }

    function GetMethod()
    {
        return $this->_method;
    }

    function GetBasePath()
    {
        return $this->_base_path;
    }

    function GetRelativePath()
    {
        return $this->_relative_path;
    }

    function &GetRequest()
    {
        return $this->_request_array;
    }

    function GetVariables()
    {
        return $this->_post_variables;
    }

    private function _DetermineMethod()
    {
        global $_SERVER;
        global $_POST;
        global $log;

        if (isset($_POST['method']))
        {
            $method = strtoupper($_POST['method']);
            unset($_POST['method']);
        }

        $this->_method = $this->_method_map[$_SERVER['REQUEST_METHOD']];

        if ($this->_method != "")
        {
            if (isset($method))
            {
                $this->_method = $this->_method_map[$method];
            }
        }
    }
}
?>
