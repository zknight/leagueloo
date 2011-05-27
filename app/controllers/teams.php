<?
namespace app;
class TeamsController extends \simp\Controller
{
    public function Setup()
    {
    }

    public function Index()
    {
        $this->StoreLocation();
        $this->program_name = $this->GetParam('program');
        //\R::debug(true);
        $program = \simp\Model::FindOne(
            'Program',
            'name = ?',
            array(ClassCase($this->program_name)));

        // all teams
        $this->teams = \simp\Model::Find(
            'Team',
            'program_id = ? order by gender, year asc',
            array($program->id));
        //\R::debug(false);

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
            $this->news = \simp\Model::Find(
                "News",
                "entity_type = 'Team' and entity_id = ?",
                array($this->team->id)
            );
            //\R::debug(false);

            $this->event_data = array('entity_type' => "Team", 'entity_id' => $this->team->id);
        }
        //\R::debug(false);
        return true;
    }
}

