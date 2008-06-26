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
      
      if (!$pointcut instanceof Sabel_Aspect_Pointcut) continue;
      
      $pointcuts = new Sabel_Aspect_DefaultPointcuts();
      $match = $pointcuts->matches($pointcut, $method, $this->target);
      
      if ($match) {
        $advices[] = $advisor->getAdvice();
      }
    }
    
    if ($match) {
      $this->invocation->setAdvices($advices);
    }
    
    return $this->invocation->proceed();
  }
  
  public function getClassName()
  {
    return get_class($this->target);
  }
}