<?php

/**
 * some class uses some class.
 * Convinience function for class loading.
 *
 * @param String $uses e.g. "user.authenticate.Test"
 */
function uses($uses)
{
  $paths = array('app/commons/models/', 'app/modules/staff/models/', 'Sabel/core/');
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
  
  if (!$loaded) throw new Exception('class can\'t find: ' . $fullpath);
}

?>