<?
class Field extends \simp\Model
{
    public $status_options;
    public function Setup()
    {
        $this->status_options = array(
            0 => 'Open',
            1 => 'Closed (Weather)',
            2 => 'Closed (Unplayable)',
            3 => 'See Comment'
            );
    }

    public function __get($property)
    {
        switch ($property)
        {
            case 'map_path':
                return "todo: fixme";
                break;
            case 'status_text':
                return $this->status_options[$this->status];
                break;
            default:
                return parent::__get($property);
        }
    }

    public function BeforeSave()
    {
        if (strlen($this->name) > 40)
        {
            $errors++;
            $this->SetError('name', "Name must be less than 40 characters");
        }

        if ($errors == 0)
        {
            $dt = new \DateTime("now");
            $this->updated_on = $dt->getTimestamp();
        }

        return $errors == 0;
    }
}
