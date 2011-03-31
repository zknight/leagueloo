<? namespace app\editor;
class NewsController extends \simp\RESTController
{
    function Setup()
    {
        $this->Model('News');
    }

    function Index()
    {
        // load all articles that this user is editor of
        $this->user = CurrentUser();
        $this->articles = \simp\Model::Find(
            'News', 
            'editor=?', 
            array($this->user->id));
        return true;
    }
}
