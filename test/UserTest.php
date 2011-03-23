<?
set_include_path(get_include_path() . PATH_SEPARATOR . "lib");
require_once "simp/KLogger.php";
$log = NULL;
require_once "simp/model.php";
require_once "app/models/user.php";
require_once "simp/utils.php";
//spl_autoload_register('__autoload');

class TestUser extends PHPUnit_Framework_TestCase 
{
    protected $backupGlobalsBlackList = array("log");
    public static function setUpBeforeClass()
    {
        //unlink("db/test.db");
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

    function testUserAdd()
    {
        echo "testUserAdd\n";
        $user = \simp\Model::Create("User");
        $user->login = "zayne";
        $this->assertEquals($user->Save(), 1);
        $user = \simp\Model::Create("User");
        $user->login = "bob";
        $this->assertEquals($user->Save(), 2);
    }

    /**
     * @depends testUserAdd
     */
    function testUserFind()
    {
        echo "testUserFind\n";
        $user = \simp\Model::Find("User", "login = ?", array("zayne"));
        $this->assertEquals($user[0]->login, "zayne");
        $this->assertEquals($user[0]->id, 1);

        $user = \simp\Model::FindOne("User", "login = ?", array("bob"));
        $this->assertEquals($user->login, "bob");
        $this->assertEquals($user->id, 2);
    }

    /**
     * @depends testUserAdd
     */
    function testUserDelete()
    {
        echo "testUserDelete()\n";
        $user = \simp\Model::FindOne("User", "login = ?", array("bob"));
        $this->assertEquals($user->Delete(), true);

        $user = \simp\Model::FindOne("User", "login = ?", array("bob"));
        $this->assertEquals($user, NULL);
    }

    /**
     * @depends testUserDelete
     */
    function testAddAbilities()
    {
        $ability = \simp\Model::Create("Ability");
        $ability->entity_type = "Program";
        $ability->entity_name = "Recreational";
        $ability->entity_id = 1;
        $ability->level = Ability::PUBLISH;
        $user = \simp\Model::FindOne("User", "login = ?", array("zayne"));
        $user->AddAbility($ability);
        $user->Save();
        $this->assertEquals($user->CanEdit("Program", 1), true);
        $this->assertEquals($user->CanPublish("Program", 1), true);
        $this->assertEquals($user->CanAdmin("Program", 1), false);
    }

    /**
     * @depends testAddAbilities
     */
    function testDeleteUserWithAbilities()
    {
        $user = \simp\Model::FindOne("User", "login = ?", array("zayne"));
        $abilities = $user->abilities;
        $this->assertNotEmpty($abilities);
        $id = $user->id;
        $user->Delete();
        $abilities = \simp\Model::Find("Ability", "user_id = ?", array($id));
        $this->assertEmpty($abilities);
    }
}
