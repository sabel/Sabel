<?php

ob_start();
$start = microtime();

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

$sstart = microtime();
include_once('../lib/setup.php');
$send = microtime();
Sabel_Core_Context::log('setup execution: '.($send - $sstart));

Sabel::main();

$end   = microtime();
Sabel_Core_Context::log('total execution: '.($end - $start));

ob_flush();