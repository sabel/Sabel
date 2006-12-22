<?php

ob_start();
define('RUN_BASE', dirname(realpath('.')));

require ('Sabel/Sabel.php');
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
