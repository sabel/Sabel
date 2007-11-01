<?php

ob_start();

define("RUN_BASE", dirname(realpath(".")));
require ("Sabel" . DIRECTORY_SEPARATOR . "Sabel.php");

$CONFIG_DIR = RUN_BASE . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR;

require ($CONFIG_DIR . "defines.php");
require ($CONFIG_DIR . "environment.php");

if (!defined("ENVIRONMENT")) {
  echo "SABEL FATAL ERROR: must define ENVIRONMENT in config/environment.php";
  exit;
}

require ($CONFIG_DIR . "Bus.php");
require ($CONFIG_DIR . "Map.php");
require ($CONFIG_DIR . "Factory.php");
require ($CONFIG_DIR . "connection.php");

if (strpos($_SERVER["SCRIPT_NAME"], "/index.php") >= 1) {
  $ignore = str_replace($_SERVER["SCRIPT_NAME"], "", $_SERVER["REQUEST_URI"]);
  define("URI_IGNORE", $ignore);
  $_SERVER["REQUEST_URI"] = ltrim($ignore, "/");
}

$config = new Config_Bus();
echo $config->configure()->getBus()->run();

ob_flush();
