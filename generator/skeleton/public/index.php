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

require_once('Sabel/Container.php');
$c = Container::create();

$dt = new DirectoryTraverser();
define('SABEL_CLASSES', RUN_BASE . '/cache/sabel_classes.php');
if (ENVIRONMENT !== 'development' && is_readable(SABEL_CLASSES)) {
  require_once(SABEL_CLASSES);
  $dt->visit(new SabelClassRegister($c));
  $dt->traverse();
} else {
  $dt->visit(new ClassCombinator(SABEL_CLASSES, null, false));
  $dt->visit(new SabelClassRegister($c));
  $dt->traverse();
  require_once(SABEL_CLASSES);
}
unset($dt);

$dt = new DirectoryTraverser(RUN_BASE);
define('APP_CACHE', RUN_BASE . '/cache/app.php');
define('LIB_CACHE', RUN_BASE . '/cache/lib.php');
define('SCM_CACHE', RUN_BASE . '/cache/schema.php');
define('INJ_CACHE', RUN_BASE . '/cache/injection.php');
if (ENVIRONMENT !== 'development' && is_readable(APP_CACHE)) {
  require_once(APP_CACHE);
  require_once(LIB_CACHE);
  require_once(SCM_CACHE);
  require_once(INJ_CACHE);
  $dt->visit(new AppClassRegister($c));
  $dt->traverse();
} else {
  $dt->visit(new ClassCombinator(APP_CACHE, RUN_BASE, false, 'app'));
  $dt->visit(new ClassCombinator(LIB_CACHE, RUN_BASE, false, 'lib'));
  $dt->visit(new ClassCombinator(SCM_CACHE, RUN_BASE, false, 'schema'));
  $dt->visit(new ClassCombinator(INJ_CACHE, RUN_BASE, false, 'injections'));
  $dt->visit(new AppClassRegister($c));
  $dt->traverse();
  require_once(APP_CACHE);
  require_once(LIB_CACHE);
  require_once(SCM_CACHE);
  require_once(INJ_CACHE);
}

create('sabel.controller.Front')->ignition();