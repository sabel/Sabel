<?php

class Sabel_Aspect_DefaultProxy extends Sabel_Aspect_AbstractProxy
{
  protected function setupInvocation()
  {
    $this->invocation = new Sabel_Aspect_DefaultMethodInvocation($this, $this->target);
  }
  
  public function __call($method, $arg)
  {
    $this->invocation->reset($method, $arg);
    
    $match = false;
    
    $advices = array();
    
    foreach ($this->advisor as $advisor) {
      $pointcut = $advisor->getPointcut();
      
      if (!$pointcut instanceof Sabel_Aspect_Pointcut)
        throw new Sabel_Exception_Runtime("pointcut must be Sabel_Aspect_Pointcut");
      
      $pointcuts = new Sabel_Aspect_DefaultPointcuts();
      
      if ($match = $pointcuts->matches($pointcut, $method, $this->target)) {
        
        $advice = $advisor->getAdvice();
        if (is_array($advice)) {
          $advices = array_merge($advice, $advices);
        } else {
          $advices[] = $advice;
        }
      }
    }
    
    if ($match) {
      $this->invocation->setAdvices($advices);
    }
    
    return $this->invocation->proceed();
  }
}