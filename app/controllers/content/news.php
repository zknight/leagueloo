<? 
namespace app\content;
//require_once "ability.php";

class NewsController extends \simp\Controller
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
        // load all articles that this user is editor of
        $this->StoreLocation();
        $this->user = CurrentUser();
        $this->published_articles = $this->user->GetPublishedNews();
        $this->unpublished_articles = $this->user->GetUnpublishedNews();
        return true;
    }

    public function Show()
    {
        $this->StoreLocation();
        $id = $this->GetParam('id');
        $this->user = CurrentUser();
        $this->article = \simp\Model::FindById("News", $id);
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
            return $this->user->OptionsForEntitiesWithPrivilege("Program,Team", \Ability::EDIT);
        }
    }

    public function Add()
    {
        $this->user = CurrentUser();
        $this->article = \simp\Model::Create('News');
        $this->entities = $this->GetEntities();
        return true;
    }

    public function Create()
    {
        $this->article = \simp\Model::Create('News');
        $vars = $this->GetFormVariable('News');
        $this->article->UpdateFromArray($vars);
        $this->article->created_on = time();
        $this->article->updated_on = $this->article->created_on;
        global $log;
        $log->logDebug("NewsController::Create() program id = {$this->article->entity_id}");
        if ($this->article->Save())
        {
            AddFlash("Article {$this->article->short_title} Created.");
            \Redirect(\Path::content_news());
        }
        else
        {
            $this->user = CurrentUser();
            //$abilities = $this->user->abilities;
            $this->entities = $this->GetEntities();
            $this->Render("Add");
            return false;
        }
    }

    public function Edit()
    {
        $this->user = CurrentUser();
        $id = $this->GetParam('id');
        $this->article = \simp\Model::FindById("News", $id);
        $this->entities = $this->GetEntities();
        return true;
    }

    public function Update()
    {

        $id = $this->GetParam('id');
        $vars = $this->GetFormVariable('News');
        $this->article = \simp\Model::FindById("News", $id);
        $this->article->UpdateFromArray($vars);
        $this->article->updated_on = time();
        if ($this->article->Save())
        {
            AddFlash("Article {$this->article->short_title} updated.");
            //\Redirect(\Path::content_news());
            \Redirect(GetReturnURL());
        }
        else
        {
            $this->user = CurrentUser();
            $this->entities = $this->GetEntities();
            $this->Render("Edit");
            return false;
        }
    }

    public function Remove()
    {
        $id = $this->GetParam('id');
        $article = \simp\Model::FindById("News", $id);
        $name = $article->short_title;
        if ($article->id > 0)
        {
            $article->Delete();
            AddFlash("Article $name deleted.");
        }
        else
        {
            AddFlash("That article is invalid.  Please contact the site administrator.");
        }
        \Redirect(\Path::content_news());
    }

    public function Publish()
    {
        $id = $this->GetParam('id');
        $article = \simp\Model::FindById("News", $id);
        $article->publish_now = true;
        if ($article->Save())
        {
            AddFlash("Article {$article->short_title} Published.");
        }
        else
        {
            AddFlash("Unable to publish {$article->short_title}.");
        }
        \Redirect(\Path::content_news());
    }

}
