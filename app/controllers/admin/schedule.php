<?
namespace app\admin;
class ScheduleController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout("admin");
        
        $this->MapAction("upload", "Load", \simp\Request::POST);
    } 

    function Index()
    {
        $this->StoreLocation();
        //$this->matches = \simp\Model::FindAll("Match");
        $this->matches = \Schedule::GetScheduleByDate();
        return true;
    }

    function Upload()
    {
        $this->schedule = new \Schedule();//\simp\Model::Create("Schedule");
        return true;
    }

    function Load()
    {
        $this->schedule = new \Schedule();//;\simp\Model::Create("Schedule");
        //$this->schedule->ImportFile($_FILES['schedule']);
        if ($_FILES['schedule']['type'] != 'text/csv')
        {
            AddFlash("File must be in CSV format.");
            $this->SetAction('upload');
        }
        else if ($this->schedule->ImportFile($_FILES['schedule']['tmp_name']))
        {
            AddFlash("{$this->schedule->new_count} Matches Added.  {$this->schedule->update_count} Matches Updated.");
            Redirect(GetReturnURL());
        }
        else
        {
            AddFlash("Failed to import.");
            $this->SetAction('upload');
        }
        return true;
    }
}
