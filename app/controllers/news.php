<?
namespace app;

class NewsController extends \simp\Controller
{
    function Setup()
    {
    }

    function Index()
    {
        $this->news = \News::FindPublished();
        return true;
    }

    function Show()
    {
        global $log;
        $log->logDebug("in NewsController::Show()");
        if ($this->GetParam("short_title"))
        {
            $entity_type = $this->GetParam('type');
            $entity_name = $this->GetParam('entity');
            $short_title = $this->GetParam('short_title');
            $this->article = \News::FindWithShortTitleByEntityName(
                $short_title, $entity_type, $entity_name);
        }
        else
        {
            $id = $this->GetParam('id');
            $this->article = \News::FindById("News", $id);
        }
        if ($this->article->id < 1)
        {
            AddFlash("article not found.");
        }
        return true;
    }
}
