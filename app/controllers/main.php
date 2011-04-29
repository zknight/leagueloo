<?
namespace app;
class MainController extends \simp\Controller
{
    
    function Setup()
    {
    }

    function Index()
    {
        \R::debug(true);
        $this->articles = \simp\Model::Find("News", "entity_type like ? and front_page = ? order by publish_date, publish_time asc ", array("Program", 1));
        return true;
    }
}
