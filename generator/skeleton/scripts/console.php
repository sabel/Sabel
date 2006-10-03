#!/opt/local/php/bin/php
<?php

$pathParts = explode('/', dirname(realpath(__FILE__)));
array_pop($pathParts);
$absolute_path = join('/', $pathParts);
define('RUN_BASE', $absolute_path);

$paths = explode(':', ini_get('include_path'));
array_shift($paths);

foreach ($paths as $path) {
  if (is_dir($path . '/Sabel')) {
    define('SABEL_USE_INCLUDE_PATH', true);
    break;
  }
}

define('SABEL_CLASSES', RUN_BASE . '/cache/sabel_classes.php');
define('APP_CACHE',     RUN_BASE . '/cache/app.php');
define('LIB_CACHE',     RUN_BASE . '/cache/lib.php');
define('SCM_CACHE',     RUN_BASE . '/cache/schema.php');
define('INJ_CACHE',     RUN_BASE . '/cache/injection.php');

require_once(RUN_BASE . '/config/environment.php');
if (!defined('ENVIRONMENT')) {
  // @todo this message to internationalization.
  print "FATAL SABEL ERROR: you must define ENVIRONMENT in config/environment.php";
  exit;
}

require_once('Sabel/Container.php');
Container::initializeApplication();

$commands = array();

while (!feof(STDIN)) {
  echo 'sabel> ';
  $in = trim(fgets(STDIN));
  if ($in === 'exit') break;
  if ($in === 'ver') {
    echo "0.1\n";
    continue;
  }
  
  if ($in[0] === '!') {
    $inLength = count($in) - 1;
    foreach ($commands as $command) {
      $in = $command;
    }
  } else {
    $commands[] = $in;
  }
  
  try {
    eval($in);
  } catch (Exception $e) {
    print $e->getMessage() . "\n";
  }
  
}