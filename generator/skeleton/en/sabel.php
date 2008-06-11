<?php

define("SBL_BATCH", true);
define("RUN_BASE",  dirname(__FILE__));

require ("Sabel"  . DIRECTORY_SEPARATOR . "Sabel.php");
require (RUN_BASE . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "INIT.php");
require (RUN_BASE . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "environment.php");

if (!defined("ENVIRONMENT")) {
  echo "SABEL FATAL ERROR: must define ENVIRONMENT in config/environment.php";
  exit;
}

$_SERVER["HTTP_HOST"]   = "localhost";
$_SERVER["REQUEST_URI"] = $_SERVER["argv"][1];

if (ENVIRONMENT === PRODUCTION) Sabel::init();

Sabel_Bus::create()->run(new Config_Bus());

if (ENVIRONMENT === PRODUCTION) Sabel::shutdown();
