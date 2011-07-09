<?
namespace app;

class NewsController extends \app\AppController
{
    function Setup()
    {
        parent::Setup();
    }

    // TODO
    // may change this later to load program and show all pubilished news just for 
    // that program!
    function Index()
    {
        $this->StoreLocation();
        $this->news = \News::FindPublished();
        return true;
    }

    function Show()
    {
        global $log;
        $this->StoreLocation();
        $log->logDebug("in NewsController::Show()");
        $log->logDebug("params: " . print_r($this->_params, true));
        $this->StoreLocation();
        if ($this->GetParam("name"))
        {
            $entity_type = $this->GetParam('type');
            $entity_name = $this->GetParam('program');
            $short_title = $this->GetParam('name');
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
        else
        {
            SetEntity(
                $this->article->entity_type,
                $this->article->entity_id,
                $this->article->entity_name
            );
        }

        return true;
    }
}
