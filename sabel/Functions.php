<?php

static $included = array();

/*
require_once('Sabel/sabel/cache/Apc.php');
$c = new Sabel_Cache_Apc();

$usesCache = array();

$rstart = microtime();
if ($c->read('uses')) {
  uses('');
} else {
  uses('sabel.cache.Apc');
  uses('sabel.controller.Front');
  uses('sabel.core.Router');
  uses('sabel.core.Dispatcher');
  uses('sabel.controller.Loader');
  uses('sabel.exception.Runtime');
  $c->write('uses', $usesCache);
}
*/

function __autoload($class)
{
  uses(convertClassPath($class));
}

/**
 * some class uses some class.
 * Convinience function for class loading.
 *
 * @param String $uses e.g. "user.authenticate.Test"
 */
function uses($uses)
{
  if (is_array($cache) && 0 < count($cache)) {
    foreach ($cache as $cpos => $path) {
      require_once($path);
    }
    return true;
  }
  
  $paths = Sabel_Core_Context::getIncludePath();
  
  $usesArray = explode('.', $uses);
  foreach ($usesArray as $idx => $name) {
    $classNames[] = ucfirst($name);
  }
  $className = implode('_', $classNames);
  $classpath = implode('/', $usesArray);
  
  if (defined('SABEL_USE_INCLUDE_PATH')) {
    foreach ($paths as $pathidx => $path) {
      $fullpath = $path.$classpath.'.php';
      require_once($path.$classpath.'.php');
    }
  } else {
    foreach ($paths as $pathidx => $path) {
      $fullpath = $path.$classpath.'.php';
      if (is_readable($fullpath)) {
        require_once($fullpath);
        break;
      }
    }
  }
  
  return $className;
}

function convertClassPath($className)
{
  $parts = explode('_', $className);
  
  if (count($parts) == 1) return $className;
  
  $classPath = '';
  for ($i = 0; $i < count($parts); $i++) {
    $last = ($i == count($parts)-1);
    $classPath .= ($last) ? $parts[$i] : strtolower($parts[$i]).'.';
  }
  
  return $classPath;
}

function is_not_null($value)
{
  return (!is_null($value));
}

?>
