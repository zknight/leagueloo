<?
class Program extends \simp\Model
{
    public function Setup()
    {
    }

    public function AfterSave()
    {
        global $log;
        $log->logDebug("Program::AfterSave()");
        /*
        if (!\simp\DB::Instance()->Find(
            'Ability', 
            'where type=? and entity=?',
            array('Program', $this->id)))
        {
            $ability = \simp\DB::Instance()->Create('Ability');
            $ability->type = 'Program';
            $ability->entity = $this->id;
            $ability->entity_name = $this->name;
            $ability->level = Ability::EDIT;
            \simp\DB::Instance()->Save($ability);

            $ability = \simp\DB::Instance()->Create('Ability');
            $ability->type = 'Program';
            $ability->entity = $this->id;
            $ability->entity_name = $this->name;
            $ability->level = Ability::PUBLISH;
            \simp\DB::Instance()->Save($ability);

            $ability = \simp\DB::Instance()->Create('Ability');
            $ability->type = 'Program';
            $ability->entity = $this->id;
            $ability->entity_name = $this->name;
            $ability->level = Ability::ADMIN;
            \simp\DB::Instance()->Save($ability);
        }
         */
    }
}
