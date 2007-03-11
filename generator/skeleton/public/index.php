<?php

define('RUN_BASE', dirname(realpath('.')));

require ('Sabel/Sabel.php');
require (RUN_BASE . '/config/environment.php');

if (!defined('ENVIRONMENT')) {
  echo "SABEL FATAL ERROR: you must define ENVIRONMENT in config/environment.php";
  exit;
}

Sabel::loadState();
$aFrontController = new Sabel_Controller_Front();

$aFrontController->processCandidate()
                 ->plugin
                 ->add(new Sabel_Controller_Plugin_Volatile())
                 ->add(new Sabel_Controller_Plugin_Filter())
                 ->add(new Sabel_Controller_Plugin_View())
                 ->add(new Sabel_Controller_Plugin_ExceptionHandler())
                 ->add(new Sabel_Controller_Plugin_Redirecter());

echo $aFrontController->ignition()->rendering();
Sabel::saveState();

exit;