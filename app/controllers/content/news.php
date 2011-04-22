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
        $this->user = CurrentUser();
        $this->published_articles = $this->user->GetPublishedNews();
        $this->unpublished_articles = $this->user->GetUnpublishedNews();
        return true;
    }

    public function Show()
    {
        $id = $this->GetParam('id');
        $this->user = CurrentUser();
        $this->article = \simp\Model::FindById("News", $id);
        return true;
    }

    public function Add()
    {
        $this->user = CurrentUser();
        $this->article = \simp\Model::Create('News');
        $abilities = $this->user->abilities;
        $this->programs = $this->user->ProgramsWithPrivilege(\Ability::EDIT);
        return true;
    }

    public function Create()
    {
        $this->article = \simp\Model::Create('News');
        $vars = $this->GetFormVariable('News');
        $this->article->UpdateFromArray($vars);
        $this->article->entity_type = "Program";
        $this->article->created_on = time();
        $this->article->updated_on = $this->article->created_on;
        //$log->logDebug("NewsController::Create() program id = {$this->article->entity_id}");
        if ($this->article->Save())
        {
            AddFlash("Article {$this->article->short_title} Created.");
            \Redirect(\Path::content_news());
        }
        else
        {
            $this->user = CurrentUser();
            //$abilities = $this->user->abilities;
            $this->programs = $this->user->ProgramsWithPrivilege(\Ability::EDIT);
            $this->Render("Add");
            return false;
        }
    }

    public function Edit()
    {
        $this->user = CurrentUser();
        $id = $this->GetParam('id');
        $this->article = \simp\Model::FindById("News", $id);
        $this->programs = $this->user->ProgramsWithPrivilege(\Ability::EDIT);
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
            \Redirect(\Path::content_news());
        }
        else
        {
            $this->user = CurrentUser();
            $this->programs = $this->user->ProgramsWithPrivilege(\Ability::EDIT);
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
