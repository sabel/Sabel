<?php

ob_start();

define("RUN_BASE", dirname(realpath(".")));

require (RUN_BASE . "/config/environment.php");
require ("Sabel/Sabel.php");
require (RUN_BASE . "/config/Bus.php");
require (RUN_BASE . "/config/Factory.php");
require (RUN_BASE . "/config/connection.php");

if (!defined("ENVIRONMENT")) {
  echo "SABEL FATAL ERROR: must define ENVIRONMENT in config/environment.php";
  exit;
}

if (strpos($_SERVER["SCRIPT_NAME"], "/index.php") >= 1) {
  $ignore = str_replace($_SERVER["SCRIPT_NAME"], "", $_SERVER["REQUEST_URI"]);
  define("URI_IGNORE", $ignore);
  $_SERVER["REQUEST_URI"] = ltrim($ignore, "/");
}

$config = new Config_Bus();
echo $config->configure()->getBus()->run();

ob_flush();
