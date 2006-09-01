<?php

error_reporting(E_ALL|E_STRICT);

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

require_once('Sabel/Container.php');
require_once('Sabel/allclasses.php');

// @todo use cache here.
$c  = new Container();
$dt = new DirectoryTraverser();
$dt->visit(new ClassRegister($c));
$dt->traverse();

$frontController = $c->load('sabel.controller.Front');
$frontController->ignition();