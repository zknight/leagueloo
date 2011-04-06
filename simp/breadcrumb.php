<?
namespace simp;

class Breadcrumb
{
    private static $_instance = NULL;
    private $_trail;

    public function __construct()
    {
        $this->_trail = array();
    }

    public static function Instance()
    {
        if (!isset(self::$_instance))
        {
            self::$_instance = new Breadcrumb();
        }
        return self::$_instance;
    }

    public function SetFromRequest($request)
    {
        // only set trail when method is a get
        if ($request->GetMethod() == Request::GET)
        {
            $req_arr = $request->GetRequest();
            global $log;
            $log->logDebug("Breadcrumb: request array =\n" . print_r($req_arr, true));
            $link = $request->GetRelativePath();
            $this->_trail = array("Main" => $link);
            foreach ($req_arr as $target)
            {
                $link .= "$target/";
                $this->_trail[$target] = $link;
            }
            $log->logDebug("Breadcrumb: request trail =\n" . print_r($this->_trail, true));
        }
    }        

    public function GetTrail()
    {
        return $this->_trail;
    }
}
