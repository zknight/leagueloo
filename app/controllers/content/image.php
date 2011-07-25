<?php
namespace app\content;

class ImageController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout('content');
        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);
        $this->MapAction("upload", "HandleArchive", \simp\Request::POST);

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
        $this->user = CurrentUser();
        $this->images = array();
        $entities = $this->user->GetEntitiesWithPrivilege(\Ability::PUBLISH, array('PlugIn'));
        foreach ($entities as $nfo)
        {
            //\R::debug(true);
            $images = \simp\Model::Find(
                'Image', 
                'entity_type = ? and entity_id = ?',
                array($nfo['type'], $nfo['id'])
            );
            //\R::debug(false);
            if (count($images) > 0)
            {
                if (!array_key_exists($nfo['type'], $this->images)) 
                    $this->images[$nfo['type']] = array();
                $this->images[$nfo['type']][$nfo['name']] = $images;
            }
        }
        return true;
    }

    public function Show()
    {
        $this->image = \simp\Model::FindById("Image", $this->GetParam("id"));
        return true;
    }

    public function Add()
    {
        $this->image = \simp\Model::Create("Image");
        $this->user = CurrentUser();
        $this->entities = $this->GetEntities();
        return true;
    }

    public function Edit()
    {
        $this->image = \simp\Model::FindById("Image", $this->GetParam('id'));
        $this->user = CurrentUser();
        $this->entities = $this->GetEntities();
        return true;
    }

    public function Create()
    {
        $this->image = \simp\Model::Create("Image");
        $vars = $this->GetFormVariable('Image');
        $this->image->UpdateFromArray($vars);
        $this->image->image_info = $_FILES['image'];
        /*
        if ($this->GetParam('format') == 'json')
        {
            global $log;
            $log->logDebug("ImageController::Create() _FILES" . print_r($_FILES, true));
            $this->image->entity_type = $this->GetFormVariable('entity_type');
            $ename = $this->GetFormVariable('entity_name');
            $this->image->entity_name = \R::getCell(
                "select id from " . SnakeCase($this->image->entity_type) . " where name = ?",
                array($ename));
            if (!$this->image->Save())
            {
                $errors = array('status' => -1, 'message' => GetErrorsFor($this->image));
                echo json_encode($errors);
                return false;
            }
            else
            {
                $ok = array(
                    'status' => 0,
                    'message' => "Image uploaded.  You still have to select it.",
                    'image' => array(
                        'width' => $this->image->width,
                        'height' => $this->image->height,
                        'thumb' => $this->image->thumb,
                        'filename' => $this->image->filename,
                        'id' => $this->image->id,
                        'path' => $this->image->path,
                    )
                );
                echo json_encode($ok);
                return false;
            }
        }
         */
        if ($this->image->Save())
        {
            AddFlash("Image {$this->image->filename} Uploaded.");
            \Redirect(GetReturnURL());
        }
        else
        {
            $this->user = $this->GetUser();
            $this->entities = $this->GetEntities();
            $this->Render("Add");
            return false;
        }
    }

    public function Update()
    {
        $this->image = \simp\Model::Create("Image");
        $vars = $this->GetFormVariable('Image');
        $this->image->UpdateFromArray($vars);
        if ($this->image->Save())
        {
            AddFlash("Image ($this->image->filename} Updated.");
            \Redirect(GetReturnURL());
        }
        else
        {
            $this->user = $this->GetUser();
            $this->entities = $this->GetEntities();
            $this->Render("Edit");
            return false;
        }
    }

    public function Remove()
    {
        $image = \simp\Model::FindById("Image", $this->GetParam('id'));
        $name = $image->filename;
        if (!$image->Delete())
        {
            AddFlash("Failed to delete image: {$name}");
        }
        else
        {
            AddFlash("Deleted image {$name}");
        }
        \Redirect(GetReturnURL());
    }

    public function Upload()
    {
        $this->user = $this->GetUser();
        $this->entities = $this->GetEntities();
        $this->archive = new \Archive();
        return true;
    }

    public function HandleArchive()
    {
        $this->archive = new \Archive();
        $this->archive->UpdateFromArray($this->GetFormVariable('Archive'));
        $this->archive->data = $_FILES['archive'];
        if ($this->archive->Save())
        {
            AddFlash("Image archive uploaded.");
            //$this->Render("handle_archive");
            //return false;
            \Redirect(\GetReturnURL());
        }
        else
        {
            $this->user = $this->GetUser();
            $this->entities = $this->GetEntities();
            global $log; $log->logDebug("HandleArchive() entities:" . print_r($this->entities, true)); 
            $this->Render("upload");
            return false;
        }
        echo "<pre>";
        print_r($this->_form_vars);
        print_r($_FILES);
        echo "</pre>";
        return false;
    }

    public function ImageList()
    {
        $this->user = $this->GetUser();
        $this->SetLayout('empty');
        $entities = $this->GetEntities();
        $this->images = array();
        foreach ($entities as $key => $val)
        {
            list ($etype, $eid) = explode(":", $key);
            list ($dummy, $ename) = explode("-", $val);
            if (!array_key_exists($etype, $this->images)) $this->images[$etype] = array();
            if (!array_key_exists($ename, $this->images[$etype])) $this->images[$etype][$ename] = array();
            $this->images[$etype][$ename] = \simp\Model::Find(
                'Image',
                'entity_type = ? and entity_id = ?',
                array($etype, $eid)
            );
        }
        /*
        $this->images = \simp\Model::Find(
            'Image', 
            'entity_type = ? and entity_id = ?',
            array(GetEntityType(), GetEntityId())
        );
         */
        return true;
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
            return $this->user->OptionsForEntitiesWithPrivilege("Main,Program,Team", \Ability::EDIT);
        }
    }
}
