<?
namespace app;
class MainController extends \simp\Controller
{
    
    function Setup()
    {
    }

    // this method will be called when there isn't a mapped action corresponding
    // to the request parameter
    function Delegate($param)
    {
        // look at request and see if it should be delgated
        // in this case, try to find a Program with the given name 
        $delegate = array('controller' => 'program', 'action' => 'show');
        return $delegate;
    }

    function Index()
    {
        return true;
    }
}
