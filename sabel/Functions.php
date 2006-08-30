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

function is_not_object($object)
{
  return (!is_object($object));
}

function urlFor()
{
  $map = new Sabel_Controller_Map();
  $map->load();
  $entry   = $map->getEntry(func_get_arg(0));
  $request = $entry->getRequest();
  
  $model = null;
  if (2 < func_num_args()) $model = func_get_arg(2);
  
  $buf = '';
  foreach ($entry->getUri() as $element) {
    $name = $element->getName();
    switch ($name) {
      case 'module':
        $buf .= '/' . $request->module;
        break;
      case 'controller':
        $buf .= '/' . $request->controller;
        break;
      case 'action':
        $buf .= '/' . func_get_arg(1);
        break;
      case 'id':
        $buf .= (is_object($model)) ? '/'.$model->$name->value : '';
        break;
    }
  }
  
  return $buf;
}
