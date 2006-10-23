<?php

class Sabel_Aspect_Interceptors_Wrapping
{
  public function when($method)
  {
    if ($method->getName() === 'selectOne' || $method->getName() === 'select') {
      return true;
    } else {
      return false;
    }
  }
  
  public function after($method, $result, $reflection)
  {
    if (is_object($result))
      return new Sabel_Aspect_DynamicProxy($result);
  }
}

Sabel_Aspect_Calls::add(new Sabel_Aspect_Interceptors_Wrapping());