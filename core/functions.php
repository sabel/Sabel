<?php

/**
 * some class uses some class.
 * Convinience function for class loading.
 *
 * @param String $uses e.g. "user.authenticate.Test"
 */
function uses($uses)
{
  $paths = SabelContext::getIncludePath();
  
  $usesArray = explode('.', $uses);
  foreach ($usesArray as $idx => $name) {
    $classNames[] = ucfirst($name);
  }
  $className = implode('_', $classNames);
  $classpath = implode('/', $usesArray);
    
  $loaded = false;
  foreach ($paths as $pathidx => $path) {
    $fullpath = $path.$classpath.'.php';
    if (is_file($fullpath)) {
      require_once($fullpath);
      $loaded = true;
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
    $first = ($i == 0);
    $last  = ($i == count($parts)-1);
    
    if ($last) {
      $classPath .= '.'. $parts[$i];
    } else if ($first) {
      $classPath .= strtolower($parts[$i]);
    } else {
      $classPath .= '.'.strtolower($parts[$i]);
    }
  }
  
  return $classPath;
}

?>