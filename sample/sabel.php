#!/opt/local/php/bin/php
<?php
$s = microtime();

$absolute_path = dirname(realpath(__FILE__));
define('RUN_BASE', $absolute_path);

$paths = explode(':', ini_get('include_path'));
array_shift($paths);

foreach ($paths as $path) {
  if (is_dir($path . '/Sabel')) {
    define('SABEL_USE_INCLUDE_PATH', true);
  }
}

require_once('lib/setup.php');

Sabel::main();
$e = microtime();
Sabel_Core_Context::log('total execution: '.($e - $s));

?>
