<?php

define('RUN_BASE', dirname(realpath('.')));

require ('Sabel/Sabel.php');
require (RUN_BASE . '/config/environment.php');

if (!defined('ENVIRONMENT')) {
  echo "SABEL FATAL ERROR: must define ENVIRONMENT in config/environment.php";
  exit;
}

$aFrontController = new Sabel_Controller_Front();
echo $aFrontController->ignition();
