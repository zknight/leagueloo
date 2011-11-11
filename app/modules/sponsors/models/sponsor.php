<?php
class Sponsor extends \simp\Model
{

    public $file_info;
    public $rel_path;
    public $abs_path;
    public $img_path;

    public function Setup()
    {
        $this->file_info = NULL;
        $this->img_path = NULL;
        global $REL_PATH;
        global $BASE_PATH;
        $path = "resources/files/img/";
        $this->rel_path = $REL_PATH . $path;
        $this->abs_path = $BASE_PATH . $path;
        $this->SkipSanity('text');

    }

    public function OnLoad()
    {
        $this->img_path = $this->rel_path . "sponsors/{$this->logo}";
    }

    public function BeforeSave()
    {
        $errors = 0;

        if ($errors == 0 && !empty($this->file_info))
        {
            $img_path = $this->abs_path . "sponsors/";
            $info = $this->file_info;
            $img_name = NULL;

            $err = ProcessImage(
                $info,
                $img_path,
                $img_name, 
                array('max_width' => 300)
            );

            if ($img_name != "") $this->logo = $img_name;

            if ($err != false)
            {
                $errors++;
                $this->SetError("logo", $err);
            }
        }

        return $errors == 0;
    }
}
