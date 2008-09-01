<?php

ob_start();

define("RUN_BASE", dirname(realpath(".")));

require ("Sabel"  . DIRECTORY_SEPARATOR . "Sabel.php");
require (RUN_BASE . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "INIT.php");
require (RUN_BASE . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "environment.php");

if (!defined("ENVIRONMENT")) {
  echo "SABEL FATAL ERROR: must define ENVIRONMENT in config/environment.php";
  exit;
}

if (strpos($_SERVER["SCRIPT_NAME"], "/index.php") >= 1) {
  define("URI_IGNORE", $ignore);
  $ignore = str_replace($_SERVER["SCRIPT_NAME"], "", $_SERVER["REQUEST_URI"]);
  $_SERVER["REQUEST_URI"] = ltrim($ignore, DS);
} elseif (isset($_GET["_uri"])) {
  define("NO_REWRITE", true);
  $_SERVER["REQUEST_URI"] = $_GET["_uri"];
}

if (ENVIRONMENT === PRODUCTION) Sabel::init();

echo Sabel_Bus::create()->run(new Config_Bus());

if (ENVIRONMENT === PRODUCTION) Sabel::shutdown();

ob_flush();
