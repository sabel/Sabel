<?php

define("RUN_BASE", realpath("."));
require ("Sabel" . DIRECTORY_SEPARATOR . "Sabel.php");

require (RUN_BASE . DS . "config" . DS . "environment.php");
require (RUN_BASE . DS . "config" . DS . "defines.php");
require (RUN_BASE . DS . "config" . DS . "Bus.php");
require (RUN_BASE . DS . "config" . DS . "Factory.php");
require (RUN_BASE . DS . "config" . DS . "connection.php");

if (!defined("ENVIRONMENT")) {
  echo "SABEL FATAL ERROR: must define ENVIRONMENT in config/environment.php";
  exit;
}

$_SERVER["HTTP_HOST"] = "localhost";
$_SERVER["REQUEST_URI"] = $_SERVER["argv"][1];

$config = new Config_Bus();
echo $config->configure()->getBus()->run();