<?php
//set_include_path(get_include_path() . PATH_SEPARATOR . "lib");
require_once "simp/cache.php";
Cache::Reset();
require_once "simp/KLogger.php";
$log = \KLogger::instance('log', \KLogger::DEBUG);
require_once "simp/request.php";
$req = new \simp\Request();

$BASE_PATH = $req->GetBasePath();
$APP_BASE_PATH = $BASE_PATH . "app";
$SIMP_BASE_PATH = $BASE_PATH . "simp";
$REL_PATH = $req->GetRelativePath();
$APP_PATH = $REL_PATH . "app";
$log->logDebug("base path: $BASE_PATH");
set_include_path(get_include_path() . PATH_SEPARATOR . "{$BASE_PATH}lib");

require_once "simp/db.php";
require_once "simp/base_model.php";
require_once "simp/model.php";
require_once "simp/helpers.php";
require_once "simp/utils.php";

date_default_timezone_set("America/Chicago");

spl_autoload_register('\simp\BaseModel::LoadModel', false);
$db_config = new \simp\DatabaseConfig();
\simp\Model::LoadDatabase($db_config->GetDSN(), $db_config->GetUser(), $db_config->GetPassword());

if (GetCfgVar("installed", false) == true)
{
    Redirect(Path::home());
}

$user = \simp\Model::Create("User");
if ($req->GetMethod() == \simp\Request::POST)
{
    $vars = $req->GetVariables();
    $user->UpdateFromArray($vars['User']);
    $user->unverified = false;
    $user->super = true;
    if ($user->Save())
    {
        SetCfgVar("installed", true);
        Redirect(Path::user_login());
    }
}
?>
<h1>Welcome to the Leagueloo Installer</h1>
<p>The first action is to setup the a first superuser account.</p>
<?= GetErrorsFor($user); ?>
<?= FormTag("install", \simp\Request::POST); ?>
    <fieldset><legend>User Information</legend>
    <table class="form">
        <tr>
            <th class="label">Login:</th><td class="entry" colspan='3'><?=TextField($user, 'login');?></td>
        </tr>
        <tr>
            <th class="label">First Name:</th><td class="entry"><?=TextField($user, 'first_name');?></td>
            <th class="label">Last Name:</th><td class="entry"><?=TextField($user, 'last_name');?></td>
        </tr>
        <tr>
            <th class="label">email:</th><td class="entry" colspan='3'><?=TextField($user, 'email', array('size' => 40));?></td>
        </tr>
        <tr>
            <th class="label">Password:</th><td class="entry" colspan='3'><?=PasswordField($user, 'password');?></td>
        </tr>
        <tr>
            <th class="label">Password Verification:</th><td class="entry" colspan='3'><?=PasswordField($user, 'password_verification');?></td>
        <tr>
            <th class="label">Timezone:</th><td class="entry" colspan='3'><?=SimpleSelect($user, 'timezone', SupportedTimeZones());?>
        </tr>
    </table><!-- TODO: add logic to prevent non-site admin to do this -->
</fieldset>
<input type="submit" value="Submit" />
<?= EndForm(); ?>

