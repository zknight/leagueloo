<?
function ClassCase($snake)
{
    $words = explode("_", $snake);
    foreach ($words as &$word)
    {
        $word = ucfirst($word);
    }
    $class = implode($words);
    return $class;
}

function HumanCase($text)
{
    $snake = SnakeCase($text);
    $words = explode("_", $snake);
    foreach ($words as &$word)
    {
        $word = ucfirst($word);
    }
    $text = implode($words, " ");
    return $text;
}


function SnakeCase($class)
{
    $snake = preg_replace("/([A-Z])/", "_$0", $class);
    $snake = trim($snake, "_");
    $words = explode("_", $snake);
    foreach ($words as &$word)
    {
        $word = strtolower($word);
    }
    $snake = implode($words, "_");
    return $snake;
}

function ToCSSSel($text)
{
    $text = preg_replace("/\s/", "_", $text);
    $text = strtolower($text);
    return $text;
}

function Pluralize($string, $count = 1)
{
    if ($count > 1)
    {
        require_once "inflect.php";
        return Inflect::pluralize($string);
    }
    return $string;
}

function Singularize($string)
{
    require_once "inflect.php";
    return Inflect::singularize($string);
}

function RedirectURI($path)
{
    session_commit();
    header("Location: " . $path);
    exit();
}

function Redirect($path)
{
    //global $REL_PATH;
    //header("Location: /" . $REL_PATH . $path);
    global $log;
    $log->logDebug("Redirecting to $path");
    session_commit();
    header("Location: " . $path);
    exit();
}

function Error404($path)
{
    global $REL_PATH;
    session_commit();
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    Redirect($REL_PATH . "public/error404.html");
    exit();
}

function RandStr($len = 8)
{
    $let =  'abcdefghijklmnopqrstuvwxyz';
    $let .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $let .= '1234567890';
    $let .= '!@#$%^&*()+=-_';
    $sz = strlen($let);
    $lettab = str_split($let);
    $rstr = "";
    for ($i = 0; $i < $len; $i++)
    {
        $rstr .= $lettab[mt_rand(0, $sz-1)];
    }
    return $rstr;
}

function GetPNGImg($img)
{
    global $log; $log->logDebug("GetPNGImg: creating image from {$img['tmp_name']}");
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
// $info: array from $_FILES for file
// $path: path to which to upload
// $name: (out) file name after upload
// $opts: array with: 'max_width' and 'max_height' and 'quality'
function ProcessImage($info, $path, &$name, $opts = array())
{
    global $log;
    $log->logDebug("ProcessImage() info: " . print_r($info, true));
    $log->logDebug("               path: $path");

    if ($info['error'] != 0 &&
        $info['error'] != UPLOAD_ERR_NO_FILE)
    {
        return "{$info['name']} upload failed: " . GetUploadError($info['error']);
    }

    if ($info['error'] == 0)
    {
        $gd_img = GetPNGImg($info);
        $filename = preg_replace('/\s/', '_', $info['name']);
        list($name, $junk) = explode('.', $filename);
        $name .= ".png";
        if ($gd_img == false)
        {
            $log->logError("ProcessImage(): {$info['name']} is type {$info['type']}, which is invalid.");
            return "File {$info['name']} is not a valid image (png, jpeg, or gif)";
        }
        
        if (!is_dir($path))
        {
            $ok = mkdir($path, 0755, true);
            if ($ok == false)
            {
                $log->logError("ProcessImage(): Failed to create path: $path");
                return "Failed to create upload path.  Contact system administrator.";
            }
        }

        $new_img = NULL;
        list($width, $height) = getimagesize($info['tmp_name']);

        $max_width = isset($opts['max_width']) ? $opts['max_width'] : $width;
        $max_height = isset($opts['max_height']) ? $opts['max_height'] : $height;
        $quality = isset($opts['quality']) ? $opts['quality'] : 4;

        $h_rat = $max_height / $height;
        $w_rat = $max_width / $width;
        
        $org_w = $width;
        $org_h = $height;
        if (($w_rat < $h_rat) && $w_rat < 1)
        {
            $height = round($height * $w_rat);
            $width = $max_width;
        }
        else if ($h_rat < 1)
        {
            $width = round($width * $h_rat);
            $height = $max_height;
        }

        $new_img = imagecreatetruecolor($width, $height);
        imagealphablending($new_img, false);
        imagesavealpha($new_img, true);
        $success = imagecopyresampled(
            $new_img,
            $gd_img,
            0,0,0,0,
            $width,
            $height,
            $org_w,
            $org_h
        );

        if (!$success)
        {

            $log->logError("ProcessImage(): Failed to resize image: $name");
            $log->logError("           original $org_w x $org_h");
            $log->logError("           new      $width x $height");
            $log->logError("           max      $max_width x $max_height");
            return "Failed to resize image.  Contact system administrator.";
        }
        else
        {
            
            if (!imagepng($new_img, $path . $name, $quality))
            {
                $log->logError("ProcessImage(): Failed to create png in path: {$path}{$name}");
                return ("Failed to copy image.  Contact system administrator.");
            }
        }
    }
    return false;
}

function GetUploadError($err)
{
    switch($err)
    {
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
        return 'File size exceeds maximum size (1MB)';
    case UPLOAD_ERR_PARTIAL:
        return 'File partially transferred (user cancel?)';
    case UPLOAD_ERR_NO_FILE:
        return 'No file specifed';
    case UPLOAD_ERR_NO_TMP_DIR:
        return 'Temporary directory not available - contact administrator';
    case UPLOAD_ERR_CANT_WRITE:
        return 'File write error - contact administrator';
    case UPLOAD_ERR_EXTENSION:
        return 'Upload stopped by extension';
    default:
        return 'Unknown error uploading file';
    }
}

function SupportedTimeZones()
{
    return array(
        'America/New_York'=>'EDT (US New York)', 
        'America/Chicago'=>'CDT (US Chicago)', 
        'America/Boise'=>'MDT (US Boise)', 
        'America/Phoenix'=>'MST (US Phoenix)', 
        'America/Los_Angeles'=>'PDT (US Los Angeles)', 
        'America/Juneau'=>'AKDT (US Juneau)', 
        'Pacific/Honolulu'=>'HST (US Honolulu)', 
        'America/Puerto_Rico'=>'AST (Puerto Rico)', 
        'Pacific/Guam'=>'ChST (Guam)', 
        'Pacific/Samoa'=>'SST (Samoa)', 
        'Pacific/Wake'=>'WAKT (Wake)',
    );
}

function DateTimeDefaultFormat()
{
    return "U";
}
?>
