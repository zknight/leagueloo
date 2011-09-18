<?
class Complex extends \simp\Model
{
    public $status_options;
    public $open;
    public $close;

    public $file_info;
    public $rel_path;
    public $abs_path;
    public $img_path;

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

        $this->file_info = NULL;
        $this->img_path = NULL;
        global $REL_PATH;
        global $BASE_PATH;
        $path = "resources/files/img/";
        $this->rel_path = $REL_PATH . $path;
        $this->abs_path = $BASE_PATH . $path;
    }

    public function OnLoad()
    {
        $this->open = unserialize($this->open_times);
        $this->close = unserialize($this->close_times);
        $this->img_path = $this->rel_path . "complex/{$this->field_map}";
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

        if ($errors == 0)
        {
            $img_path = $this->abs_path . "complex/";
            $info = $this->file_info;
            $img_name = NULL;
            global $log; $log->logDebug("Complex::BeforeSave() map file type is {$info['type']}");
            if ($info['type'] == "application/pdf")
            {
                $err = ProcessPDF(
                    $info,
                    $img_path,
                    $img_name
                );
            }
            else 
            {
                $err = ProcessImage(
                    $info,
                    $img_path,
                    $img_name,
                    array('max_width' => 100)
                );
            }
            if ($img_name != "") $this->field_map = $img_name;

            if ($err != false)
            { 
                $errors++;
                $this->SetError("field_map", $err);
            }
        }

        return $errors == 0;
    }

}
