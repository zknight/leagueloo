<?
namespace app;
class MainController extends \app\AppController
{
    
    function Setup()
    {
        global $log;
        $log->logDebug('In: MainController::Setup()');
        parent::Setup();
    }

    function Index()
    {
        SetEntity('Main', 0, 'Club');
        $this->StoreLocation();
        //\R::debug(true);
        /*
        if (\Cache::Exists('programs'))
        {
            $programs = \Cache::Read('programs');
        }
        else
        {
            $programs = \simp\Model::FindAll('Program', 'order by weight asc');
        }
         */
        //global $log; $log->logDebug("programs: " . print_r($this->program_names, true));
        $this->articles = array();
        $this->is_editor = array();
        $user = NULL;
        if ($this->UserLoggedIn())
        {
            $user = $this->GetUser();
        }
        /*
        $this->articles["Club"] = \News::FindPublished(
            'Main',
            0,
            '1 order by publish_on, entity_id asc'
        );
         */
        //$this->is_editor['Club'] = isset($user) ? $user->CanEdit("Main", 0) : false;
        /*
        foreach ($programs as $program)
        {
            $this->articles[$program->name] = \News::FindPublished(
                'Program',
                $program->id,
                'front_page = ? order by publish_on, entity_id asc',
                array(true)
            );
                //"News", 
                //"entity_type like ? and front_page = ? and entity_id = ?" .
                //" order by publish_on, entity_id asc ", 
                //array("Program", 1, $program->id));
            $this->is_editor[$program->name] = isset($user) ? $user->CanEdit("Program", $program->id) : false;
        }
         */
        $this->articles = \News::FindPublished(
            'Main',
            0,
            '1 order by publish_on, entity_id asc'
        );

        $this->articles = array_merge($this->articles, \News::FindPublished(
            'Program',
            NULL,
            'front_page = ? order by publish_on asc',
            array(true)
        ));
        return true;
    }

    public function Page()
    {
        SetEntity('Main', 0, 'Club');
        $this->StoreLocation();
        // expect short_title or id?
        if ($name = $this->GetParam("name"))
        {
            $this->page = \simp\Model::FindOne(
                "Page", 
                "entity_type = ? and entity_id = ? and short_title = ? and published = ?", 
                array("Main", 0, $name, true)
            );
        }
        else
        {
            $id = $this->GetParam('id');
            $this->page = \simp\Model::FindById("Page", $id);
        }
        return true;
    }

    public function About()
    {
        $this->page = \simp\Model::FindOne("Page", "name = ?", array("club_information"));
        return true;
    }
}
