<?php

define("RUN_BASE", dirname(realpath(".")));

require ("Sabel/Sabel.php");
require (RUN_BASE . "/config/environment.php");

if (!defined("ENVIRONMENT")) {
  echo "SABEL FATAL ERROR: must define ENVIRONMENT in config/environment.php";
  exit;
}

if (strpos($_SERVER["SCRIPT_NAME"], "index.php") !== false) {
  define("URI_IGNORE", true);
  $ignore = str_replace($_SERVER["SCRIPT_NAME"], "", $_SERVER["REQUEST_URI"]);
  $_SERVER["REQUEST_URI"] = ltrim($ignore, "/");
} elseif (substr($_SERVER["SCRIPT_NAME"], 0, 7) !== "/public") {
  define("URI_IGNORE", true);
  $ignore = str_replace("public/index.php", "", $_SERVER["SCRIPT_NAME"]);
  $_SERVER["REQUEST_URI"] = str_replace($ignore, "", $_SERVER["REQUEST_URI"]);
}

$aFrontController = new Sabel_Controller_Front();
$response = $aFrontController->ignition();
$response->outputHeaderIfRedirectedThenExit();
echo Sabel_View::renderDefault($response);
