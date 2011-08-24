<?
class Complex extends \simp\Model
{
    public $status_options;
    public $open;
    public $close;

    protected $_fields;
    public function Setup()
    {
        $this->status_options = array(
            0 => 'Open',
            1 => 'Closed (Inclement Weather)',
            2 => 'Closed (Fields Unplayable)',
            3 => 'Closed',
            4 => 'See Comment'
            );
        $this->_fields = array();
    }

    public function OnLoad()
    {
        $this->open = unserialize($this->open_times);
        $this->close = unserialize($this->close_times);
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
            case 'fields':
                if (empty($this->_fields) && $this->id > 0)
                {
                    $this->_fields = \simp\Model::Find(
                        "Field", 
                        "complex_id = ? order by dimensions, name asc",
                        array($this->id)
                    );
                }
                return $this->_fields;
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

        // TODO: ensure valid times
        $this->open_times = serialize($this->open);
        $this->close_times = serialize($this->close);

        return $errors == 0;
    }

}
