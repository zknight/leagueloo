<?php
class Coach extends \simp\Model
{
    // fields
    // ------
    // first_name
    // last_name
    // title (staff, doc, asst doc, academy director, etc.)
    // coaching_history
    // playing_experience
    // years_with_club
    // license
    // bio
    // pic
    // teams (links to teams) (how to do this?)

    static public $license_levels = array(
        0 => 'E Certificate',
        1 => 'State D License',
        2 => 'USSF D License',
        3 => 'USSF C License',
        4 => 'USSF B License',
        5 => 'USSF A License',
        6 => 'USSF Goalkeeping License',
        7 => 'USSF Fitness License',
        8 => 'USYS Youth License',
        9 => 'USSF Adult License'
    );

    static public $coach_titles = array(
        0 => 'Director of Coaching',
        1 => 'Assistant Director of Coaching',
        2 => 'Director of Academy',
        3 => 'Assistant Director of Academy',
        4 => 'Director of Goalkeeping',
        5 => 'Senior Staff Coach',
        6 => 'Staff Coach',
        7 => 'Junior Staff Coach',
        8 => 'Fitness Trainer',
        9 => 'Trainer'
    );

    public $file_info;
    public $rel_path;
    public $abs_path;
    public $img_path;
    public $assoc_teams;
    public $teams;
    protected $unassoc_teams;
    protected $teams_to_associate;

    public function Setup()
    {
        $this->file_info = NULL;
        $this->img_path = NULL;
        $this->teams = array();
        $this->assoc_teams = array();
        $this->unassoc_teams = array();
        $this->teams_to_associate = array();
        global $REL_PATH;
        global $BASE_PATH;
        $path = "resources/files/img/";
        $this->rel_path = $REL_PATH . $path;
        $this->abs_path = $BASE_PATH . $path;
        $this->SkipSanity('coaching_history', 'playing_experience', 'bio');
    }

    public function __set($property, $value)
    {
        switch ($property)
        {
        case "license":
            $this->licenses = serialize($value);
            break;
        default:
            parent::__set($property, $value);
            break;
        }
    }

    public function __get($property)
    {
        switch ($property)
        {
        case "license":
            return unserialize($this->licenses);
            break;
        case "coaching_licenses":
            $licenses = array();
            foreach ($this->license as $level => $valid)
            {
                if ($valid) $licenses[] = Coach::$license_levels[$level];
            }
            return $licenses;
        default:
            return parent::__get($property);
            break;
        }
    }

    public function OnLoad()
    {
        $this->img_path = $this->rel_path . "coach_pics/{$this->pic}";
        $team_beans = \R::related($this->_bean, 'team');
        foreach ($team_beans as $id => $bean)
        {
            $this->teams[$id] = new Team($bean);
            $this->assoc_teams[$id] = true;
        }
  
    }

    public function BeforeSave()
    {
        $errors = 0;
        foreach ($this->assoc_teams as $id => $assoc)
        {
            if ($assoc && !isset($this->teams[$id]))
                $this->teams_to_associate[$id] = \simp\Model::FindById('Team', $id);
            else if (!$assoc && array_key_exists($id, $this->teams))
                $this->unassoc_teams[] = $this->teams[$id];
        }

        if (!$this->VerifyNotEmpty('first_name')) $errors++;
        if (!$this->VerifyNotEmpty('last_name')) $errors++;
        if (!$this->VerifyNotEmpty('title')) $errors++;

        if ($errors == 0)
        {
            foreach ($this->file_info as $filetype => $info)
            {
                if ($info['error'] != 0 && 
                    $info['error'] != UPLOAD_ERR_NO_FILE) 
                {
                    $errors++;
                    $this->SetError($filetype, HumanCase($filetype) ." must be a valid image (png, jpeg, or gif).");
                }

                if ($errors == 0 && $info['error'] != UPLOAD_ERR_NO_FILE)
                {
                    $gd_img = GetPNGImg($info);
                    $filename = preg_replace('/\s/', '_', $info['name']);
                    list ($this->$filetype, $junk) = explode('.', $filename);
                    $this->$filetype .= ".png";
                    if ($gd_img == false)
                    {
                        $errors++;
                        $this->SetError($filetype, "File {$info['name']} must be valid image: jpg, png, or gif");
                    }
                    else if ($this->CopyPic($gd_img, $info, $this->$filetype) == false)
                    {
                        $errors++;
                    }
                }
            }
        }


        return $errors == 0;
    }

    public function AfterSave()
    {
        foreach ($this->teams_to_associate as $team)
        {
            global $log; $log->logDebug("Coach: associating to {$team->name}");
            \R::associate($this->_bean, $team->Bean());
        }
        foreach ($this->unassoc_teams as $team)
        {
            global $log; $log->logDebug("Coach: unassociating from {$team->name}");
            \R::unassociate($this->_bean, $team->Bean());
        }
    }

    protected function CopyPic($img, $info, $name)
    {
        $img_path = $this->abs_path . "coach_pics/";
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
        $max_width = 100;
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

}
