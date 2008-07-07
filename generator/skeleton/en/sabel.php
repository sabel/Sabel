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
$_SERVER["SERVER_NAME"] = "localhost";

if (isset($_SERVER["argv"][2])) {
  $_SERVER["REQUEST_METHOD"] = strtoupper($_SERVER["argv"][2]);
} else {
  $_SERVER["REQUEST_METHOD"] = "GET";
}

if (isset($_SERVER["argv"][1])) {
  $parsed = parse_url("http://localhost/" . $_SERVER["argv"][1]);
  $_SERVER["REQUEST_URI"] = $parsed["path"];
  
  if (isset($parsed["query"])) {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      parse_str($parsed["query"], $_POST);
    } else {
      parse_str($parsed["query"], $_GET);
    }
  }
} else {
  $_SERVER["REQUEST_URI"] = "/";
}

if (ENVIRONMENT === PRODUCTION) Sabel::init();

Sabel_Bus::create()->run(new Config_Bus());

if (ENVIRONMENT === PRODUCTION) Sabel::shutdown();
