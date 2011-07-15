<?php
namespace app\content;

class LinkController extends \simp\Controller
{
    public function Setup()
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
        $this->links = array();
        $entities = $this->GetUser()->GetEntitiesWithPrivilege(\Ability::PUBLISH, array('PlugIn'));
        foreach ($entities as $nfo)
        {
            $links = \simp\Model::find(
                'Link',
                'entity_type = ? and entity_id = ? order by disabled asc',
                array($nfo['type'], $nfo['id'])
            );
            if (count($links) > 0)
            {
                if (!array_key_exists($nfo['type'], $this->links))
                    $this->links[$nfo['type']] = array();
                $this->links[$nfo['type']][$nfo['name']] = $links;
            }
        }
        return true;
    }

    public function Show()
    {
        $this->link = \simp\Model::FindById("Link", $this->GetParam("id"));
        return true;
    }

    public function Add()
    {
        $this->link = \simp\Model::Create("Link");
        $this->entities = $this->GetEntities();
        return true;
    }

    public function Edit()
    {
        $this->link = \simp\Model::FindById("Link", $this->GetParam("id"));
        $this->entities = $this->GetEntities();
        return true;
    }

    public function Create()
    {
        $this->link = \simp\Model::Create("Link");
        $vars = $this->GetFormVariable('Link');
        $this->link->UpdateFromArray($vars);

        if ($this->link->Save())
        {
            AddFlash("Link '{$this->link->text}' Created.");
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
        $this->link = \simp\Model::FindById("Link", $this->GetParam("id"));
        $vars = $this->GetFormVariable('Link');
        $this->link->UpdateFromArray($vars);
        if ($this->link->Save())
        {
            AddFlash("Link '{$this->link->text}' Updated.");
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
        $link = \simp\Model::FindById("Link", $this->GetParam("id"));
        $text = $link->text;
        if (!$link->Delete())
        {
            AddFlash("Failed to delete link: '{$text}'");
        }
        else
        {
            AddFlash("Deleted link: '{$text}'");
        }
        \Redirect(GetReturnURL());
    }

    protected function GetEntities()
    {
        if ($this->CheckParam('entity') && $this->CheckParam('entity_id'))
        {
            $entity = $this->GetParam('entity');
            $id = $this->GetParam('entity_id');
            if ($this->user->CanEdit($entity, $id))
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
            //$abilities = $this->user->abilities;
            return $this->GetUser()->OptionsForEntitiesWithPrivilege("Main,Program,Team", \Ability::PUBLISH);
        }
    }
}
