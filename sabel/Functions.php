<?php

require_once('Sabel/sabel/core/Resolver.php');

function __autoload($class)
{
  uses(Sabel_Core_Resolver::resolvClassPathByClassName($class));
}

/**
 * some class uses some class.
 * Convinience function for class loading.
 *
 * @param String $uses e.g. "user.authenticate.Test"
 */
function uses($uses)
{
  $paths = Sabel_Core_Context::getIncludePath();
  
  $className = Sabel_Core_Resolver::resolvClassName($uses);
  $classpath = Sabel_Core_Resolver::resolvPath($uses);
  
  foreach ($paths as $path) {
    $fullpath = $path . $classpath . '.php';
    if (defined('SABEL_USE_INCLUDE_PATH')) {
      require_once($fullpath);
    } else {
      (is_readable($fullpath)) ? require_once($fullpath) : null;
    }
  }
}

function is_not_null($value)
{
  return (!is_null($value));
}
