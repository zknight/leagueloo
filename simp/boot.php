<?php
namespace simp;
require_once "KLogger.php";
require_once "router.php";
require_once "request.php";
require_once "controller.php";

$log = \KLogger::instance('log', \KLogger::DEBUG);
$req = new Request();

$APP_BASE_PATH = $req->GetBasePath() . "app";
$APP_PATH = $req->GetRelativePath() . "app";

// TODO: cache this!
$router = new Router();

?>

