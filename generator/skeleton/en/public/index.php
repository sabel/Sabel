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
  $ignore = str_replace($_SERVER["SCRIPT_NAME"], "", $_SERVER["REQUEST_URI"]);
  $_SERVER["REQUEST_URI"] = ltrim($ignore, "/");
  define("URI_IGNORE", $ignore);
} elseif (isset($_GET[NO_REWRITE_PREFIX])) {
  $_uri = substr(ltrim($_SERVER["REQUEST_URI"], "/"), strlen(NO_REWRITE_PREFIX) + 2);
  $_SERVER["REQUEST_URI"] = $_uri;
  $parsed = parse_url($_SERVER["REQUEST_URI"]);
  if (isset($parsed["query"])) {
    parse_str($parsed["query"], $_GET);
  }
  
  unset($_GET[NO_REWRITE_PREFIX]);
  define("NO_REWRITE", true);
}

if ((ENVIRONMENT & PRODUCTION) > 0) {
  Sabel::init();
  echo Sabel_Bus::create()->run(new Config_Bus());
  Sabel::shutdown();
} else {
  echo Sabel_Bus::create()->run(new Config_Bus());
}

ob_flush();
