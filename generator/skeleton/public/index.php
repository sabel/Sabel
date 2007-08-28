<?php

ob_start();

define("RUN_BASE", dirname(realpath(".")));
require ("Sabel" . DIRECTORY_SEPARATOR . "Sabel.php");

require (RUN_BASE . DS . "config" . DS . "environment.php");
require (RUN_BASE . DS . "config" . DS . "Bus.php");
require (RUN_BASE . DS . "config" . DS . "Factory.php");
require (RUN_BASE . DS . "config" . DS . "connection.php");

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
