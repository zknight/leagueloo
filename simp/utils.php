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

function Pluralize($string)
{
    require_once "inflect.php";
    return Inflect::pluralize($string);
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
