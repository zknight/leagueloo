<?
namespace app;
class ContentController extends \app\AppController
{
    function Setup()
    {
        $this->RequireAuthorization('index');
        $this->SetLayout('content');
    }

    protected function CheckAccess()
    {
        if (!$this->GetUser()->super)
        {
            AddFlash("You don't have sufficient privilege for this action.");
            \Redirect(GetReturnURL());
        }
    }

    public function Index()
    {
        $this->StoreLocation();
        $tournament = \simp\Model::FindOne("Program", "type = ?", array(\Program::TOURNAMENT));
        $camp = \simp\Model::FindOne("Program", "type = ?", array(\Program::CAMP));
        $this->tournament_id = $tournament->id;
        $this->camp_id = $camp->id;
        return true;
    }

}
