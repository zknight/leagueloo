<?
namespace simp;

class RESTController extends Controller
{

    protected $_model;

    function __construct($router)
    {
        parent::__construct($router);
        $this->Setup();
    }

    function Model($model_name)
    {
        $this->$model = $model_name;
    }

    function Dispatch($request)
    {
        global $log;
        $path = '';
        $controller_name = '';
        $log->logDebug("Dispatching with request:\n " . print_r($request->GetRequest(), true));

        $this->_method = $request->GetMethod();

        $done = $this->CheckDefault($request);

        if (!isset($_model)) die("Model not set for RESTController " . get_class($this));



    }
}
