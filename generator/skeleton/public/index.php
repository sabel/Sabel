<?php

$absolute_path = dirname(realpath(__FILE__));
$tmp_path = explode('/', $absolute_path);
array_pop($tmp_path);
$absolute_path = join('/', $tmp_path);
define('RUN_BASE', $absolute_path);

$paths = explode(':', ini_get('include_path'));
array_shift($paths);

foreach ($paths as $path) {
  if (is_dir($path . '/Sabel')) define('SABEL_USE_INCLUDE_PATH', true);
}

require_once(RUN_BASE . '/config/environment.php');
if (!defined('ENVIRONMENT')) {
  // @todo this message to internationalization.
  print "FATAL SABEL ERROR: you must define ENVIRONMENT in config/environment.php";
  exit;
}

define('SABEL_CLASSES', RUN_BASE . '/cache/sabel_classes.php');
define('APP_CACHE',     RUN_BASE . '/cache/app.php');
define('LIB_CACHE',     RUN_BASE . '/cache/lib.php');
define('SCM_CACHE',     RUN_BASE . '/cache/schema.php');
define('INJ_CACHE',     RUN_BASE . '/cache/injection.php');

require_once('Sabel/Container.php');
Container::initializeApplication()->load('sabel.controller.Front')->ignition();