<?
namespace app;
class MainController extends \simp\Controller
{
    
    function Setup()
    {
    }

    function Index()
    {
        //\R::debug(true);
        $programs = \simp\Model::FindAll('Program', 'order by weight asc');
        //global $log; $log->logDebug("programs: " . print_r($this->program_names, true));
        $this->articles = array();
        foreach ($programs as $program)
        {
            $this->articles[$program->name] = \simp\Model::Find(
                "News", 
                "entity_type like ? and front_page = ? and entity_id = ?" .
                " order by publish_on, entity_id asc ", 
                array("Program", 1, $program->id));
        }
        return true;
    }
}
