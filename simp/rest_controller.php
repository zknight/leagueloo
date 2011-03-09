<?
require_once "controller.php"

class RESTController extends Controller
{

    protected $_model;

    function __construct($router)
    {
        parent::__construct($router);
        $this->Setup();
    }
}
