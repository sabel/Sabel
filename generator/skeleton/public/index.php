<?php

if (extension_loaded('gettext')) {
  function _($val)
  {
    return $val;
  }
}

ob_start();

set_include_path('/usr/local/lib/php/Sabel' . ':' . '/usr/local/lib/php');
require ('../config/environment.php');
require ('../setup.php');
require ('/usr/local/lib/php/Sabel/Sabel.php');
set_include_path(RUN_BASE . '/app' . ':' . get_include_path());
set_include_path(RUN_BASE . '/lib' . ':' . get_include_path());

$response = Sabel::load('Sabel_Controller_Front')->ignition();

echo $response['html'];

ob_flush();