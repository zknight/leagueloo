<?php
namespace simp;
require_once "KLogger.php";
$log = \KLogger::instance('log', \KLogger::DEBUG);
$log->logDebug('\\/ \\/ \\/');

require_once "router.php";
require_once "request.php";
require_once "controller.php";
//require_once "rest_controller.php";

$req = new Request();

$APP_BASE_PATH = $req->GetBasePath() . "app";
$APP_PATH = $req->GetRelativePath() . "app";

// TODO: cache this!
$router = new Router();
$router->Route($req);

$log->logDebug('/\\ /\\ /\\');
?>

