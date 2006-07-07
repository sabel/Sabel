<?php

require_once('core/Context.php');

/**
 * some class uses some class.
 * Convinience function for class loading.
 *
 * @param String $uses e.g. "user.authenticate.Test"
 */
function uses($uses)
{
  $paths = Sabel_Core_Context::getIncludePath();
  
  $usesArray = explode('.', $uses);
  foreach ($usesArray as $idx => $name) {
    $classNames[] = ucfirst($name);
  }
  $className = implode('_', $classNames);
  $classpath = implode('/', $usesArray);
    
  foreach ($paths as $pathidx => $path) {
    $fullpath = $path.$classpath.'.php';
    if (is_file($fullpath)) {
      require_once($fullpath);
      break;
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

?>
