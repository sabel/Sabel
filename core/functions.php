<?php

/**
 * some class uses some class.
 * Convinience function for class loading.
 *
 * @param String $name e.g. user.authenticate.test
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

/*
function uses($className)
{
  $paths = array('app/commons/models/', 'app/modules/staff/models/');
  
  foreach ($paths as $pathidx => $path) {
    require_once('app/commons/models/mail/operation/Generic.php');
    require_once($path . $className . '.php');
    break;
  }
}
*/

?>