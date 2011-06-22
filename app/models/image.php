<?
/// Image model
///
/// image directory is resources/files/img/<entity_type>/<entity_name>/
/// thumb directory is resources/files/img/<entity_type>/<entity_name>/thumbs/
///
/// entity_id
/// entity_name
/// entity_type
/// filename
/// width
/// height

class Image extends \simp\Model
{

    //global $REL_PATH;
    protected $path;
    protected $rel_path;
    protected $abs_path;
    public $image_info;

    public function Setup()
    {
        global $REL_PATH;
        global $BASE_PATH;
        $this->path = "resources/files/img/";
        $this->rel_path = $REL_PATH . $this->path;
        $this->abs_path = $BASE_PATH . $this->path;
        $this->image_info = NULL;
    }

    public function __get($property)
    {
        switch($property)
        {
        case "thumb":
            return $this->rel_path . "{$this->entity_type}/{$this->entity_name}/thumbs/{$this->filename}";
            break;
        case "path":
            return $this->rel_path . "{$this->entity_type}/{$this->entity_name}/{$this->filename}";
            break;
        case 'entity_name':
            if ($this->entity_id == 0)
            {
                return "Club";
            }
            return \R::getCell(
                "select name from " . SnakeCase($this->entity_type) . " where id = ?", 
                array($this->entity_id));
            break;
        case "entity_designator":
            $entity_designator = "{$this->entity_type}:{$this->entity_id}";
            global $log; $log->logDebug("returning entity_designator: $entity_designator");
            return $entity_designator;
            break;
        default:
            return parent::__get($property);
            break;
        }
    }
    public function __set($property, $value)
    {
        switch($property)
        {
        case "entity_designator":
            global $log;
            $log->logDebug("setting entity_designator from: $value");
            list($this->entity_type, $this->entity_id) = explode(":", $value);
            $log->logDebug("entity_type: {$this->entity_type}");
            $log->logDebug("entity_id: {$this->entity_id}");
            break;
        default:
            return parent::__set($property, $value);
            break;
        }
    }

    public function BeforeSave()
    {
        // process the image!
        $errors = 0;
        if ($this->image_info['error'] != 0)
        {
            $this->SetError('image', GetUploadError($this->image_info['error']));
            return false;
        }
        $gd_img = $this->GetImg($this->image_info);
        $this->filename = preg_replace('/\s/', '', $this->filename);
        if ($this->filename == NULL || $this->filename == '')
        {
            $this->filename = $this->image_info['name'];
        }
        //preg_replace('/(?\.png)|(?\.jpg)|(?\.gif)|(?\.jpeg)/', '', $this->filename);
        list($this->filename, $junk) = explode(".", $this->filename);
        $this->filename .= ".png";
        if ($gd_img == false)
        {
            $errors++;
            $this->SetError('image', "File {$this->filename} must be valid image: jpeg, png, gif");
        }
        else
        {
            list($this->width, $this->height) = getimagesize($this->image_info['tmp_name']);
            $w = $this->width;
            $h = $this->height;
            $ratio = $this->width / $this->height;
            // TODO: make this configurable (by gallery?)
            $thumb_w = 100;
            $max_width = 400;
            $new_img = NULL;
            $thumb = NULL;
            if ($this->width > $max_width)
            {
                $this->height = round($max_width / $ratio);
                $this->width = $max_width;
                AddFlash("Image scaled from $w x $h to {$this->width} x {$this->height} (ratio $ratio)");
            }
            $th = round($thumb_w / $ratio);
            $tw = $thumb_w;
            $new_img = imagecreatetruecolor($this->width, $this->height);
            $thumb = imagecreatetruecolor($tw, $th);
            $success = 
                imagecopyresampled(
                    $new_img,
                    $gd_img,
                    0, 0, 0, 0, $this->width, $this->height, $w, $h) &&
                imagecopyresampled(
                    $thumb,
                    $gd_img,
                    0, 0, 0, 0, $tw, $th, $w, $h);
            if ($success == true)
            {
                if (!$this->CopyPics($new_img, $thumb))
                {
                    $errors++;
                    $this->SetError('image', "Failed to save image.");
                }
            }
            else
            {
                $errors++;
                $this->SetError('image', "Failed to process image.");
            }
        }

        return $errors == 0;
    }

    public function BeforeDelete()
    {
        // delete the photo and thumb
        $img_path = $this->abs_path . "{$this->entity_type}/{$this->entity_name}/";
        $thumb_path = "{$img_path}thumbs/";
        unlink($img_path . $this->filename);
        unlink($thumb_path . $this->filename);
        return true;
    }

    protected function GetImg($img)
    {
        global $log; $log->logDebug("GetImg: creating image from {$img['tmp_name']}");
        switch($img['type'])
        {
        case "image/png":
        case "image/x-png": // ie
            return imagecreatefrompng($img['tmp_name']);
            break;
        case "image/gif":
            return imagecreatefromgif($img['tmp_name']);
            break;
        case "image/jpeg":
        case "image/pjpeg": // ie
            return imagecreatefromjpeg($img['tmp_name']);
            break;
        default:
            return FALSE;
        }
    }

    protected function CopyPics($img, $thumb)
    {
        $img_path = $this->abs_path . "{$this->entity_type}/{$this->entity_name}/";
        $thumb_path = "{$img_path}thumbs/";
        if (!is_dir($img_path))
        {
            $ok = mkdir($img_path, 0755, true);
            if ($ok == false) {
                $this->SetError('image', "Failed to create image path.  Contact sysadmin.");
                return false;
            }
        }
        if (!is_dir($thumb_path))
        {
            $ok = mkdir($thumb_path, 0755, true);
            if ($ok == false) {
                $this->SetError('image', "Failed to create thumbnail path.  Contact sysadmin.");
                return false;
            }
        }
        if (!imagepng($img, $img_path . $this->filename , 4))
        {
            $this->SetError('image', "Failed to copy image. Contact sysadmin.");
            return false;
        }
        if (!imagepng($thumb, $thumb_path . $this->filename, 9))
        {
            $this->SetError('image', "Failed to copy thumbnail.  Contact sysadmin.");
            return false;
        }

        return true;
    }
}


