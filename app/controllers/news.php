<?
namespace app;

class NewsController extends \simp\RestController
{
    function Setup()
    {
        $this->Model("News");
    }

    function Index()
    {
        $this->news = \News::FindPublished();
        return true;
    }

    function Show()
    {
        if ($this->GetParam(0) == "short_title")
        {
            $entity_type = $this->GetParam(1);
            $entity_name = $this->GetParam(2);
            $short_title = $this->GetParam(3);
            $this->article = \News::FindWithShortTitleByEntityName(
                $short_title, $entity_type, $entity_name);
        }
        else
        {
            $id = $this->GetParam(0);
            $this->article = \News::FindById("News", $id);
        }
        return true;
    }
}
