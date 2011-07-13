<?php
namespace simp;
//$TEST = true;
require_once "cache.php";
require_once "KLogger.php";
$log = \KLogger::instance('log', \KLogger::DEBUG);
$log->logDebug('---------START---------');

require_once "router.php";
require_once "request.php";
require_once "controller.php";
require_once "rest_controller.php";



$req = new Request();

$BASE_PATH = $req->GetBasePath();
$APP_BASE_PATH = $BASE_PATH . "app";
$SIMP_BASE_PATH = $BASE_PATH . "simp";
$REL_PATH = $req->GetRelativePath();
$APP_PATH = $REL_PATH . "app";
$log->logDebug("base path: $BASE_PATH");
set_include_path(get_include_path() . PATH_SEPARATOR . "{$BASE_PATH}lib");
$log->logDebug("include path: " . get_include_path());
require_once "db.php";
require_once "base_model.php";
require_once "dummy_model.php";
require_once "model.php";
require_once "email.php";
require_once "module.php";
spl_autoload_register('\simp\Module::LoadModule', false);
spl_autoload_register('\simp\BaseModel::LoadModel', false);
//Model::LoadDatabase("sqlite:db/development.db");
$db_config = new DatabaseConfig();
Model::LoadDatabase($db_config->GetDSN(), $db_config->GetUser(), $db_config->GetPassword());
require_once "session.php";
date_default_timezone_set(GetCfgVar("default_timezone", "America/Chicago"));

if (GetCfgVar("installed", false) == false)
{
    \Redirect(\Path::relative("install.php"));
}


// TODO: cache this!
$router = new Router();
require_once "app/routes.php";
RouteSetup($router);
$router->Route($req);

session_commit();
$log->logDebug('---------STOP---------\n\n');

?>

