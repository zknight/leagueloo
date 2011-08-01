<?php
namespace app;

class PageController extends \app\AppController
{
    public function Setup()
    {
        parent::Setup();
    }

    function Index()
    {
        $ename = $this->GetParam('program');
        \Redirect(\Path::relative("{$ename}"));
    }

    function Show()
    {
        $this->StoreLocation();
        if ($this->GetParam("name"))
        {
            $entity_type = $this->GetParam('type');
            $entity_name = $this->GetParam('program');
            $short_title = $this->GetParam('name');
            $this->page = \Page::FindWithShortTitleByEntityName($short_title, $entity_type, $entity_name);
        }
        else
        {
            $id = $this->GetParam('id');
            $this->page = \Page::FindById("Page", $id);
        }
        if ($this->page->id < 1)
        {
            AddFlash("page not found.");
        }
        else
        {
            SetEntity(
                $this->page->entity_type,
                $this->page->entity_id,
                $this->page->entity_name
            );
        }
        return true;
    }
}

