<?php

class Sabel_Aspect_Proxy
{
  protected $name = '';
  protected $target = null;
  
  public function __construct($taget)
  {
    $this->target = $taget;
  }
  
  public function __call($method, $arg)
  {
    $aspects = Sabel_Aspect_Aspects::singleton();
    $matches = $aspects->findMatch(array('method' => $method));
    
    $reflection = new ReflectionClass($this->target);
    $method = $reflection->getMethod($method);
    
    try {
      return $method->invokeArgs($this->target, $arg);
    } catch (Exception $e) {
      foreach ($matches as $aspect) {
        $aspectReflect = new ReflectionClass($aspect);
        if ($aspectReflect->hasMethod('afterThrowing')) {
          return $aspect->afterThrowing($e, $arg, $this->target);
        }
      }
    }
  }
  
  public function __set($property, $value)
  {
    
  }
  
  public function __get($property)
  {
  }
}