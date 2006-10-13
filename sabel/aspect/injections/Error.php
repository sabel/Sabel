<?php

class Sabeo_Aspect_Injections_Error
{
  public function when()
  {
    return true;
  }
  
  public function before($method, $arg, $reflection, $target)
  {
    if ($method->getName() === 'save') {
      $className = $reflection->getName();
      $v = new Sabel_Validate_Model();
      $v->initializeSchema(strtolower($className));
      $errors = $v->validate($arg[0]);
      if ($errors->hasError()) {
        Re::set(strtolower($className), $target);
        Re::set('errors', $errors);
        return false;
      } else {
        // $target->save($arg[0]);
        $method->invokeArgs($target, $arg);
        Sabel_Core_Context::getPageController()->redirectToPrevious();
        return false;
        // already terminated.
      }
    }
  }
}

Sabel_Aspect_Calls::add(new Sabeo_Aspect_Injections_Error());