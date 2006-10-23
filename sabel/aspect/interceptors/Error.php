<?php

class Sabel_Aspect_Interceptors_Error
{
  public function when($method)
  {
    return ($method->getName() === 'save');
  }
  
  public function before($method, $arg, $reflection, $target)
  {
    $className = $reflection->getName();
    $v = new Sabel_Validate_Model();
    $v->initializeSchema(strtolower($className));
    $errors = $v->validate($arg[0]);
    if ($errors->hasError()) {
      Re::set(strtolower($className), $target);
      Re::set('errors', $errors);
      return false;
    } else {
      $method->invokeArgs($target, $arg);
      Sabel_Core_Context::getPageController()->redirectToPrevious();
      return false;
    }
  }
}

Sabel_Aspect_Calls::add(new Sabel_Aspect_Interceptors_Error());