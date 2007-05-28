<?php

define('RUN_BASE', dirname(realpath('.')));

require ('Sabel/Sabel.php');
require (RUN_BASE . '/config/environment.php');

if (!defined('ENVIRONMENT')) {
  echo "SABEL FATAL ERROR: must define ENVIRONMENT in config/environment.php";
  exit;
}

$aFrontController = new Sabel_Controller_Front();

$aFrontController->processCandidate()
                 ->plugin
                 ->add(new Sabel_Controller_Plugin_Filter())
                 ->add(new Sabel_Controller_Plugin_View())
                 ->add(new Sabel_Controller_Plugin_Flow())
                 ->add(new Sabel_Controller_Plugin_Exception())
                 ->add(new Sabel_Controller_Plugin_Redirecter());
                 
$aFrontController->ignition();
echo $aFrontController->getResult();
