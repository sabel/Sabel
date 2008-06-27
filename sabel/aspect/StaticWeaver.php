<?php

class Sabel_Aspect_StaticWeaver implements Sabel_Aspect_Weaver
{
  private $target = null;
  
  private $advisor = array();
  
  public function __construct($target = null)
  {
    if ($target !== null) {
      $this->target = $target;  
    }
  }
  
  public function setTarget($target)
  {
    $this->target = $target;
  }
  
  public function addAdvisor($advisor, $position = null)
  {
    if ($position === null) {
      $position = count($this->advisor);
    }
    
    $this->advisor[$position] = $advisor;
  }
  
  public function getProxy()
  {
    if ($this->target === null) {
      throw new Sabel_Exception_Runtime("must be set target class");
    }
    
    if (!is_object($this->target)) {
      if (class_exists($this->target)) {
        $this->target = new $this->target();
      }
    }
    
    $adviced    = new Sabel_Aspect_Adviced();
    $reflection = new Sabel_Reflection_Class($this->target);
    
    foreach ($this->advisor as $advisor) {
      $pointcut = $advisor->getPointcut();
      
      if (!$pointcut instanceof Sabel_Aspect_Pointcut)
        throw new Sabel_Exception_Runtime("pointcut must be Sabel_Aspect_Pointcut");
      
      $pointcuts = new Sabel_Aspect_DefaultPointcuts();
      
      foreach ($reflection->getMethods() as $method) {
        if ($pointcuts->matches($pointcut, $method->getName(), $this->target)) {
          $adviced->addAdvices($method->getName(), $advisor->getAdvice());
        }
      }
    }
    
    if ($adviced->hasAdvices()) {
      $proxy = new Sabel_Aspect_StaticProxy($this->target);
      $proxy->setAdviced($adviced);
      
      return $proxy;
    } else {
      // no match found. return a raw target object.
      return $this->target;
    }
  }
}