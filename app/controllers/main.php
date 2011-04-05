<?
namespace app;
class MainController extends \simp\Controller
{
    
    function Setup()
    {
    }

    // this method will be called when there isn't a mapped action corresponding
    // to the request parameter
    function Delegate($request)
    {
        global $log;
        $log->logDebug("MainController::Delegate() - request: \n" . print_r($request, true));
        // look at request and see if it should be delgated
        // in this case, try to find a Program with the given name 
        // valid requests:
        // <program_name>
        // <program_name>/<article_name>
        $req_arr = $request->GetRequest();
        switch (count($req_arr))
        {
        case 1:
            $delegate = array('program', 'name');
            break;
        case 2:
            $delegate = array('news', 'short_title', 'Program');
            break;
        default:
            $delegate = null;
        }
        //$delegate = array('controller' => 'program', 'action' => 'show');
        return $delegate;
    }

    function Index()
    {
        return true;
    }
}
