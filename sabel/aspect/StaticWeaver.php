<?php

class Sabel_Aspect_StaticWeaver
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
    
    $reflection = new Sabel_Reflection_Class($this->target);
    
    $matchedMethods = array();
    
    foreach ($this->advisor as $advisor) {
      $pointcut = $advisor->getPointcut();
      $methodMatcher = $pointcut->getMethodMatcher();
      
      foreach ($reflection->getMethods() as $method) {
        $match = $methodMatcher->matches($method->getName(), $this->target);
        
        if ($match) {
          $matchedMethods[] = array("method" => $method->getName(),
                                    "advice" => $advisor->getAdvice());
        }
      }
    }
    
    if (count($matchedMethods) >= 1) {
      return new Sabel_Aspect_StaticProxy($this->target);
    } else {
      return $this->target;  
    }
  }
}