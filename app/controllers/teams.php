<?
namespace app;
class TeamsController extends \app\AppController
{
    public function Setup()
    {
        parent::Setup();
    }

    public function Index()
    {
        $this->StoreLocation();
        $this->program_name = $this->GetParam('program');
        //\R::debug(true);
        $this->program = \simp\Model::FindOne(
            'Program',
            'name = ?',
            array(ClassCase($this->program_name)));

        // all teams
        $teams = \simp\Model::Find(
            'Team',
            'program_id = ? order by gender, year asc',
            array($this->program->id));
        //\R::debug(false);
        
        $this->teams = array();
        foreach ($teams as $team)
        {
            if (!array_key_exists($team->gender, $this->teams))
                $this->teams[$team->gender] = array();
            $this->teams[$team->gender][] = $team;
        }
        // TODO: make sure entity is set in all the right places
        // and add name to SetEntity()
        SetEntity('Program', $this->program->id, $this->program->name);

        return true;
    }

    public function ByGender()
    {
        $this->StoreLocation();
        return true;
    }

    public function ByDivision()
    {
        $this->StoreLocation();
        return true;
    }

    public function Team()
    {
        $this->StoreLocation();
        $gender = $this->GetParam('gender');
        $division = $this->GetParam('division');
        $name = preg_replace("/_/", ' ', $this->GetParam('name'));

        //\R::debug(true);
        $this->team = \simp\Model::FindOne(
            'Team',
            'gender = ? and year = ? and name = ?',
            array(
                $gender,
                \Team::GetYearFromDivision($division),
                $name)
            );

        if ($this->team->id > 0)
        {
            //\R::debug(true);
            $this->news = \News::FindPublished(
                //"entity_type = 'Team' and entity_id = ?",
                'Team',
                $this->team->id
            );
            //\R::debug(false);

            //$this->event_data = array('entity_type' => "Team", 'entity_id' => $this->team->id);
            SetEntity('Team', $this->team->id, $this->team->name);
        }
        //\R::debug(false);
        return true;
    }
}

