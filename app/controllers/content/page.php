<?php
namespace app\content;

class PageController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout('content');
        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);

        $this->RequireAuthorization(
            array(
                'index',
                'show',
                'add',
                'edit',
                'delete'
            )
        );
    }

    public function Index()
    {
        $this->StoreLocation(); 
        $this->published_pages = \Page::GetPublishedPages($this->GetUser());
        $this->unpublished_pages = \Page::GetUnpublishedPages($this->GetUser());
        return true;
    }

    public function Add()
    {
        $this->page = \simp\Model::Create('Page');
        $this->entities = $this->GetEntities();
        return true;
    }

    public function Edit()
    {
        $this->page = \simp\Model::FindById('Page', $this->GetParam('id'));
        $this->entities = $this->GetEntities();
        return true;
    }

    public function Create()
    {
        $this->page = \simp\Model::Create('Page');
        $this->page->UpdateFromArray($this->GetFormVariable('Page'));
        if ($this->page->Save())
        {
            /*
            AddRecentUpdate(
                'page',
                $this->page->id,
                $this->page->title,
                $this->page->entity_name,
                "page",
                "show",
                $this->page->short_title);
             */
            AddFlash("Page {$this->page->short_title} Created.");
            \Redirect(GetReturnURL());
        }
        else
        {
            $this->entities = $this->GetEntities();
            $this->SetAction("add");
        }
        return true;
    }

    public function Update()
    {
        $this->page = \simp\Model::FindById('Page', $this->GetParam('id'));
        $this->page->UpdateFromArray($this->GetFormVariable('Page'));
        if ($this->page->Save())
        {
            /*
            AddRecentUpdate(
                'page',
                $this->page->id,
                $this->page->title,
                $this->page->entity_name,
                "page",
                "show",
                $this->page->short_title);
             */
            AddFlash("Page {$this->page->short_title} Updated.");
            \Redirect(GetReturnURL());
        }
        else
        {
            $this->entities = $this->GetEntities();
            $this->SetAction("edit");
        }
        return true;
    }

    public function Remove()
    {
        $page = \simp\Model::FindById('Page', $this->GetParam('id'));
        $name = $page->short_title;
        if ($page->id > 0)
        {
            $page->Delete();
            AddFlash("Page $name deleted.");
        }
        else
        {
            AddFlash("That page is invalid.  Please contact the site administrator.");
        }
        \Redirect(GetReturnURL());
    }

    protected function GetEntities()
    {
        if ($this->CheckParam('entity') && $this->CheckParam('entity_id'))
        {
            $entity = $this->GetParam('entity');
            $id = $this->GetParam('entity_id');
            if ($this->GetUser()->CanEdit($entity, $id))
            {
                $entity = \simp\Model::FindById($entity, $id);
                return array("{$entity}:$id" => "{$entity}-{$entity->name}");
            }
            else
            {
                AddFlash("You do not have privileges for that.");
                Redirect(GetReturnURL());
            }
        }
        else
        {
            return $this->GetUser()->OptionsForEntitiesWithPrivilege("Main,Program,Team", \Ability::PUBLISH);
        }
    }
}
