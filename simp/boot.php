<?php
namespace simp;
//$TEST = true;
require_once "KLogger.php";
$log = \KLogger::instance('log', \KLogger::DEBUG);
$log->logDebug('\\/ \\/ \\/');

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

set_include_path(get_include_path() . PATH_SEPARATOR . "{$BASE_PATH}lib");
require_once "model.php";
require_once "email.php";
require_once "module.php";
spl_autoload_register("\simp\Module::LoadModule", false);
spl_autoload_register("\simp\Model::LoadModel", false);
\simp\Model::LoadDatabase("sqlite:db/development.db");
require_once "session.php";
date_default_timezone_set(GetCfgVar("default_timezone", "America/Chicago"));

// TODO: cache this!
$router = new Router();
require_once "app/routes.php";
RouteSetup($router);
$router->Route($req);

session_commit();
$log->logDebug('/\\ /\\ /\\');

?>

