<?php
class Archive extends \simp\DummyModel
{

    public function BeforeSave()
    {
        global $log;
        //  [name] => sgfdumper.tgz
        //  [type] => application/x-compressed-tar
        //  [tmp_name] => /tmp/phpEwgs6b
        //  [error] => 0
        //  [size] => 242853
        if ($this->data['error'] != 0)
        {
            $this->SetError('archive', "Failed to upload archive {$this->data['name']}");
            return false;
        }

        // look at type and extract
        global $BASE_PATH;
        $upload_dir = $BASE_PATH . "uploads/archive/";
        global $log; $log->logDebug("checking directory: $upload_dir");
        if (!is_dir($upload_dir))
        {
            $ok = mkdir($upload_dir, 0755, true);
            if ($ok == false)
            {
                $this->SetError('archive', "Failed to create upload path.  Contact sysadmin.");
                return false;
            }
        }
        $dest = $upload_dir . $this->data['name'];
        $log->logDebug("moving {$this->data['tmp_name']} to {$dest}");
        if (!move_uploaded_file($this->data['tmp_name'], $dest))
        {
            $this->SetError('archive', "Failed to upload file.");
        }
        switch($this->data['type'])
        {
        case 'application/x-compressed-tar':
        case 'application/x-tar':
            $ok = $this->Untar($dest);
            break;
        case 'application/zip':
            break;
        case 'application/x-gzip':
            break;
        }

        if ($ok == true)
        {
            // create an image for each file
            foreach ($this->files as $file)
            {
                $log->logDebug("ImageFile from archive: $file");
                $name_ar = explode("/", $file);
                $name = $name_ar[count($name_ar)-1];
                list($gbg, $ext) = explode(".", $name);
                $type = FALSE;
                switch ($ext)
                {
                case 'jpg':
                case 'jpeg':
                    $type = "image/jpeg";
                    break;
                case 'gif':
                    $type = "image/gif";
                    break;
                case 'png':
                    $type = "image/png";
                    break;
                default: 
                    $log->logDebug("name $name: $gbg $ext");
                }

                if (!$type) continue;

                $vars = array(
                    "entity_designator" => $this->entity_designator,
                    "image_info" => array(
                        'error' => 0,
                        'tmp_name' => $file,
                        'name' => $name,
                        'type' => $type
                    )
                );

                $image = \simp\Model::Create("Image");
                $image->UpdateFromArray($vars);
                if (!$image->Save())
                {
                    $errors = $image->GetErrors();
                    foreach ($errors as $error)
                    {
                        $this->SetError("archive", $error);
                    }
                    return false;
                }
                else
                {
                    unlink($file);
                }
            }
        }

        return $ok;
    }

    protected function Untar($file)
    {
        $cmd = "tar xvf {$file} -C tmp";
        $output = array();
        exec($cmd, $output, $ret);
        global $log; $log->logInfo(print_r($output, true));
        $log->logDebug("$cmd returned $ret"); 
        foreach ($output as &$file)
        {
            $file = "tmp/" . $file;
        }
        $this->files = $output;
        return $ret == 0;
    }

    protected function ScanForImages($dir)
    {
    }
}
