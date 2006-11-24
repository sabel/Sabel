<?php

function _($val)
{
  return $val;
}

ob_start();

require ('../config/environment.php');
require ('../setup.php');
require ('Sabel/Sabel.php');
Sabel::initializeApplication();
require (APP_CACHE);
require (LIB_CACHE);
require (SCM_CACHE);
require (INJ_CACHE);

$front = new Sabel_Controller_Front();
$response = $front->ignition();

ob_flush();