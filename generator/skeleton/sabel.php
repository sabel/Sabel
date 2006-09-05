<?php

$absolute_path = dirname(realpath(__FILE__));
define('RUN_BASE', $absolute_path);

$paths = explode(':', ini_get('include_path'));
array_shift($paths);

foreach ($paths as $path) {
  if (is_dir($path . '/Sabel')) {
    define('SABEL_USE_INCLUDE_PATH', true);
  }
}

require_once(RUN_BASE . '/config/environment.php');
if (!defined('ENVIRONMENT')) {
  // @todo this message to internationalization.
  print "FATAL SABEL ERROR: you must define ENVIRONMENT in config/environment.php";
  exit;
}

require_once('Sabel/Container.php');
$c = Container::create();

$dt = new DirectoryTraverser();
define('SABEL_CLASSES', RUN_BASE . '/cache/sabel_classes.php');
if (ENVIRONMENT !== 'development' && is_readable(SABEL_CLASSES)) {
  require_once(SABEL_CLASSES);
  $dt->visit(new ClassRegister($c));
  $dt->traverse();
} else {
  $dt->visit(new ClassCombinator(SABEL_CLASSES, null, false));
  $dt->visit(new ClassRegister($c));
  $dt->traverse();
  require_once(SABEL_CLASSES);
}
unset($dt);

$dt = new DirectoryTraverser(RUN_BASE);
define('APP_CACHE', RUN_BASE.'/cache/app.php');
if (ENVIRONMENT !== 'development' && is_readable(APP_CACHE)) {
  require_once(APP_CACHE);
  $dt->visit(new ClassRegister($c));
  $dt->traverse();
} else {
  $dt->visit(new ClassCombinator(APP_CACHE, RUN_BASE, false, 'app'));
  $dt->visit(new ClassRegister($c));
  $dt->traverse();
  require_once(APP_CACHE);
}

$frontController = $c->load('sabel.controller.Front');
$frontController->ignition();