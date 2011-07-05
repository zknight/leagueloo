<?php
/// Program model
///
/// A Program is a main section of a Leagueloo site.
/// Examples are: Recreational Program, Competitive Program, Camp Program
/// Programs have associated news and events.
class Program extends \simp\Model
{

    const LEAGUE = 0;
    const TOURNAMENT = 1;
    const CAMP = 2;
    public static $types = array(
        Program::LEAGUE => 'league',
        Program::TOURNAMENT => 'tournament',
        Program::CAMP => 'camp',
    );

    public $file_info;
    protected $rel_path;
    protected $abs_path;
    public $img_path;

    // TODO: factor these out
    public $editors;
    public $publishers;
    public $admins;

    public function Setup()
    {
        $this->file_info = NULL;
        $this->img_path = NULL;
        global $REL_PATH;
        global $BASE_PATH;
        $path = "resources/files/img/";
        $this->rel_path = $REL_PATH . $path;
        $this->abs_path = $BASE_PATH . $path;
        $this->SkipSanity('description');
    }

    public function OnLoad()
    {
        $this->img_path = $this->rel_path . "Program/{$this->name}/{$this->image}";
    }

    public function BeforeSave()
    {
        $this->allow_teams = $this->type == 0;
        $errors = 0;

        if (!$this->VerifyNotEmpty('name')) $errors++;

        if ($errors == 0)
        {
            $img_path = $this->abs_path . "Program/{$this->name}/";
            $info = $this->file_info;
            $name = NULL;
            $err = ProcessImage(
                $info,
                $img_path,
                $name,
                array('max_height' => 315, 'max_width' => 420)
            );

            if ($name != "") $this->image = $name;

            if ($err != false)
            { 
                $errors++;
                $this->SetError("image", $err);
            }
        }

        return $errors == 0;
    }

    public function Type()
    {
        return Program::$types[$this->type];
    }

    /*
    protected function CopyPic($img, $info, $name)
    {
        $img_path = $this->abs_path . "program/{$this->name}/";
        if (!is_dir($img_path))
        {
            $ok = mkdir($img_path, 0755, true);
            if ($ok == false)
            {
                $this->SetError("image", "Failed to create Coach's image path.  Contact sysadmin.");
                return false;
            }
        }

        // TODO: make this configurable
        $max_width = 450;
        $new_img = NULL;
        list($width, $height) = getimagesize($info['tmp_name']);
        $w = $width;
        $h = $height;
        $ratio = $width / $height;
        if ($width > $max_width)
        {
            $height = round($max_width/$ratio);
            $width = $max_width;
        }
        $new_img = imagecreatetruecolor($width, $height);
        $success = imagecopyresampled(
            $new_img,
            $img,
            0,0,0,0,
            $width,
            $height,
            $w,
            $h);
        if ($success == true)
        {
            if (!imagepng($new_img, $img_path . $name, 4))
            {
                $this->SetError('image', "Failed to copy image.  Contact sysadmin.");
                return false;
            }
        }
        else
        {
            $this->SetError('image', "Failed to process image.");
            return false;
        }

        return true;
    }
     */

}
