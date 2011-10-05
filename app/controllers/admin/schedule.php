<?
namespace app\admin;
class ScheduleController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            array(
                'index',
                'add',
                'show',
                'edit',
                'delete',
                'fields',
                'upload',
                'download',
                'load',
                'addmatch',
                'modifymatch',
            )
        );

        $this->MapAction("upload", "Load", \simp\Request::POST);
        $this->MapAction("download", "Send", \simp\Request::POST);
        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("addmatch", "Creatematch", \simp\Request::PUT);
        $this->MapAction("modifymatch", "Updatematch", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);
    } 

    function Index()
    {
        $this->StoreLocation();
        //$this->matches = \simp\Model::FindAll("Match");
        $now = new \DateTime("now");
        $this->schedules = \simp\Model::Find("Schedule", "end_date >= ?", array($now->getTimestamp()));
        $this->past_schedules = \simp\Model::Find("Schedule", "end_date < ?", array($now->getTimestamp()));
        //$this->matches = \Schedule::GetScheduleByDate();
        return true;
    }

    function Add()
    {
        $this->schedule = \simp\Model::Create("Schedule");
        return true;
    }

    function Edit()
    {
        $this->schedule = \simp\Model::FindById("Schedule", $this->GetParam('id'));
        return true;
    }

    function Create()
    {
        $this->schedule = \simp\Model::Create("Schedule");
        $vars = $this->GetFormVariable("Schedule");
        $this->schedule->UpdateFromArray($vars);
        if ($this->schedule->Save())
        {
            \AddFlash("Schedule added.  Now create matches or upload");
            \Redirect(\GetReturnURL());
        }
        else
        {
            $this->SetAction("add");
        }

        return true;
    }

    function Update()
    {
        $this->schedule = \simp\Model::FindById("Schedule", $this->GetParam('id'));
        $vars = $this->GetFormVariable("Schedule");
        $this->schedule->UpdateFromArray($vars);
        if ($this->schedule->Save())
        {
            \AddFlash("Schedule updated.");
            \Redirect(\GetReturnURL());
        }
        else
        {
            $this->SetAction("edit");
        }
        return true;
    }

    function Remove()
    {
        $this->schedule = \simp\Model::FindById("Schedule", $this->GetParam('id'));
        $this->schedule->Delete();
        AddFlash("Schedule Removed.");
        \Redirect(\GetReturnURL());
    }


    function Show()
    {
        $this->StoreLocation();
        $this->schedule = \simp\Model::FindById("Schedule", $this->GetParam('id'));
        return true;
    }

    function Fields()
    {
        $this->StoreLocation();
        $this->schedule = new \Schedule();
        return true;
    }

    function Upload()
    {
        $this->schedule = \simp\Model::FindById("Schedule", $this->GetParam('id'));
        //$this->schedule = new \Schedule();//\simp\Model::Create("Schedule");
        return true;
    }

    function Download()
    {
        $this->formats = array("GotSoccer", "Arbiter", "Leagueloo");
        $this->scheds = array();
        $schedules = \simp\Model::FindAll("Schedule");
        foreach ($schedules as $s)
        {
            $this->scheds[$s->id] = $s->name;
        }
        return true;
    }        

    function Load()
    {
        //$this->schedule = new \Schedule();//;\simp\Model::Create("Schedule");
        $this->schedule = \simp\Model::FindById("Schedule", $this->GetParam('id'));
        $vars = $this->GetFormVariable('Schedule');
        //$this->schedule->level = $vars['level'];
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

    function Send()
    {
        //echo "<pre>";
        //print_r($this->_form_vars);
        $sched = $this->GetFormVariable('sched');
        $start = $this->GetFormVariable('start');
        $stop = $this->GetFormVariable('stop');
        $format = $this->GetFormVariable('format');

        $start_dt = $start != NULL ? new \DateTime($start) : new \DateTime("1/1/1970");
        $stop_dt = $stop != NULL ? new \DateTime($stop) : new \DateTime("12/31/2037");
        
        $q = "SELECT id FROM division where schedule_id in (" . implode(",", $sched) . ")";
        //$dids = \R::getAll($q);
        $dids = \R::getCol($q);
        //print_r($dids);
        $matches = \simp\Model::Find("game", "date >= ? and date <= ? and division_id in (" . implode(",", $dids) .")", 
            array($start_dt->getTimestamp(), $stop_dt->getTimestamp()));
        //print_r($matches);

        switch ($format)
        {
        case 0: // GotSoccer
            break;
        case 1: // Arbiter
            $rows = array();
            $rows[] = array(
                "Date", "Time", "Game", "Sport", "Level", "Home-Team", "Home-Level",
                "Away-Team", "Away-Level", "Site", "Sub-site", "Bill-To", "Officials");
            foreach ($matches as $m)
            {
                // TODO: Fix "SLSC" to be configurable
                $rows[] = array(
                    $m->date_str, $m->start_time, "", "SLSC", "{$m->age} {$m->gender} {$m->division}",
                    "{$m->home_club} {$m->home}", "", "{$m->away_club} {$m->away}", "",
                    $m->field->gotsoccer_name, "", "", ""
                );
            }

            break;
        case 2: // Leagueloo
            break;
        }

        ob_start();
        foreach ($rows as $row)
        {
            header('Content-type: txt/plain');
            header('Content-Disposition: attachment; filename="schedule_arb.csv"');
            echo implode(",", $row) . "\n";
        }
        $content = ob_get_contents();
        ob_end_clean();
        echo $content;
        return false;
    }

    public function Addmatch()
    {
    }

    public function Creatematch()
    {
    }

    public function Modifymatch()
    {
        $this->game = \simp\Model::FindById("Game", $this->GetParam('id'));
        $division = \simp\Model::FindById("Division", $this->game->division_id);
        $this->field_opts = array();
        foreach ($division->fields as $f)
        {
            $this->field_opts[$f->id] = $f->name;
        }

        return true;
    }

    public function Updatematch()
    {
        $this->game = \simp\Model::FindById("Game", $this->GetParam('id'));
        $vars = $this->GetFormVariable('Game');
        $this->game->UpdateFromArray($vars);
        $this->game->field_name = $this->game->field->gotsoccer_name;
        $this->game->home_full_name = "{$this->game->home_club} {$this->game->home}";
        $this->game->away_full_name = "{$this->game->away_club} {$this->game->away}";

        if (!$this->game->Save())
        {
            $division = \simp\Model::FindById("Division", $this->game->division_id);
            $this->field_opts = array();
            foreach ($division->fields as $f)
            {
                $this->field_opts[$f->id] = $f->name;
            }
            $this->SetAction("modifymatch");
        }
        else
        {
            AddFlash("Match {$this->game->gotsoccer_id} updated.");
            \Redirect(\GetReturnURL());
        }

        return true;
    }
}
