<?
namespace simp;

class RESTController extends Controller
{

    protected $_model;

    function __construct()
    {
        parent::__construct();
    }

    function Model($model_name)
    {
        $this->_model = $model_name;
    }

    function CheckAction(&$request)
    {
        global $log;
        $handled = false;
        $request_params = &$request->GetRequest();
        $action = '';

        // no params: 
        //  GET => Index
        //  POST => Create
        // params:
        //  GET => 
        //      id => Show
        //      'edit' => Edit
        //      'new' => New
        //  PUT => 
        //      id => Update
        //  DELETE => 
        //      id => Remove
        $log->logDebug("REST CheckAction() request_params: \n" . print_r($request_params, true));
        if (count($request_params) > 0)
        {
            $log->logDebug("REST method: " . $request->GetMethod());
            switch($request->GetMethod())
            {
            case Request::GET:
                switch($request_params[0])
                {
                case 'edit':
                    $action = 'Edit';
                    array_shift($request->GetRequest());
                    break;
                case 'new':
                case 'add':
                    $action = 'Add';
                    array_shift($request->GetRequest());
                    break;
                default:
                    $action = 'Show';
                    break;
                }
                break;
            case Request::PUT:
                $action = 'Update';
                break;
            case Request::DELETE:
                $action = 'Remove';
                break;
            }
            $log->logDebug("REST action: " . $action);
        }
        else if ($request->GetMethod() == Request::POST)
        {
            $action = 'Create';
        }

        if (method_exists($this, $action))
        {
            $handled = true;
            $this->_action = $action;
        }
        else 
        {
            $handled = parent::CheckAction($request);
        }

        return $handled;
    }

}
