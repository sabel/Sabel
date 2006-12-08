<?php

ob_start();
set_include_path('/usr/local/lib/php/Sabel:/usr/local/lib/php');
define('RUN_BASE', dirname(realpath('.')));

require ('Sabel.php');
require (RUN_BASE . '/config/environment.php');

if (!defined('ENVIRONMENT')) {
  echo "SABEL FATAL ERROR: you must define ENVIRONMENT in config/environment.php";
  exit;
}

Sabel::loadState();
$response = Sabel::load('Sabel_Controller_Front')->ignition();
Sabel::saveState();

echo $response['html'];
ob_flush();