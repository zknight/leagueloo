<?
set_include_path(get_include_path() . PATH_SEPARATOR . "lib");
require_once "simp/KLogger.php";
$log = NULL;
require_once "simp/model.php";
require_once "app/models/user.php";
require_once "simp/utils.php";

class Test extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        \simp\Model::LoadDatabase("sqlite:db/test.db");
    }

    public function setUp()
    {
        global $log;
        $log = \KLogger::instance('log', \KLogger::DEBUG);
    }

    public static function tearDownAfterClass()
    {
        unlink("db/test.db");
    }
}
