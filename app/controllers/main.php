<?
namespace app;
class MainController extends \simp\Controller
{
    
    function Setup()
    {
    }

    function Index()
    {
        SetEntity('Main', 0, 'Club');
        $this->StoreLocation();
        //\R::debug(true);
        $programs = \simp\Model::FindAll('Program', 'order by weight asc');
        //global $log; $log->logDebug("programs: " . print_r($this->program_names, true));
        $this->articles = array();
        $this->is_editor = array();
        $user = NULL;
        if ($this->UserLoggedIn())
        {
            $user = $this->GetUser();
        }
        $this->articles["Club"] = \News::FindPublished(
            'Main',
            0,
            '1 order by publish_on, entity_id asc'
        );
        $this->is_editor['Club'] = isset($user) ? $user->CanEdit("Main", 0) : false;

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
        return true;
    }
}
