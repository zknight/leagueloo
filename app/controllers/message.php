<?
namespace app;
class MessageController extends \app\AppController
{
    function Setup()
    {
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);
        $this->RequireAuthorization(
            array(
                'index',
                'show',
                'delete'
            )
        );
    }

    function Index()
    {
        $this->StoreLocation();
        $this->user = $this->GetUser();
        $this->messages = \simp\Model::Find(
            'Message', 
            "user_id = ?",// order by date asc", 
            array($this->user->id));
        return true;
    }

    function Show()
    {
        $this->user = $this->GetUser();
        $this->message = \simp\Model::FindById(
            'Message',
            $this->GetParam('id'));
        $this->message->unread = false;
        $this->message->Save();
        return true;
    }

    function Remove()
    {
        $message = \simp\Model::FindById(
            'Message',
            $this->GetParam('id'));

        $message->Delete();
        \Redirect(\GetReturnURL());
    }

}
