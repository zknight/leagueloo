<? 
namespace app\content;
//require_once "ability.php";

class NewsController extends \simp\RESTController
{
    function Setup()
    {
        $this->Model('News');
        $this->RequireAuthorization(
            array(
                'Index',
                'show',
                'Add',
                'Create',
                'Edit',
                'Update',
                'Remove'
            )
        );
    }

    function Index()
    {
        // load all articles that this user is editor of
        $this->user = CurrentUser();
        $this->published_articles = $this->user->GetPublishedNews();
        $this->unpublished_articles = $this->user->GetUnpublishedNews();
        return true;
    }

    function Show()
    {
        $id = GetParam(0);
        $this->user = CurrentUser();
        $this->article = \simp\Model::FindById("News", $id);
        return true;
    }

    function Add()
    {
        $this->user = CurrentUser();
        $this->article = \simp\Model::Create('News');
        $abilities = $this->user->abilities;
        $this->programs = $this->user->ProgramsWithPrivilege(\Ability::EDIT);
        return true;
    }

    function Create()
    {
        $this->article = \simp\Model::Create('News');
        $vars = $this->GetFormVariable('News');
        $this->article->UpdateFromArray($vars);
        $this->article->entity_type = "Program";
        //$log->logDebug("NewsController::Create() program id = {$this->article->entity_id}");
        if ($this->article->Save())
        {
            AddFlash("Article {$this->article->short_name} Created.");
            \Redirect(\Path::content_news());
        }
        else
        {
            $this->user = CurrentUser();
            $abilities = $this->user->abilities;
            $this->programs = $this->user->ProgramsWithPrivilege(\Ability::EDIT);
            $this->Render("Add");
            return false;
        }
    }

}
