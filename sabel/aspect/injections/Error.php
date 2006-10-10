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
      $v = new Sabel_Validate_Model();
      $v->initializeSchema(strtolower($reflection->getName()));
      $errors = $v->validate($arg[0]);
      if ($errors->hasError()) {
        Re::set('bbs', $target);
        Re::set('errors', $errors);
        return false;
      } else {
        Sabel_Core_Context::getPageController()->redirectToPrevious();
        // already terminated.
      }
    }
  }
}

Sabel_Aspect_Calls::add(new Sabeo_Aspect_Injections_Error());