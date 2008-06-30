<?php

class Sabel_Aspect_DefaultProxy extends Sabel_Aspect_AbstractProxy
{
  protected function __setupInvocation()
  {
    $this->invocation = new Sabel_Aspect_DefaultMethodInvocation($this, $this->target);
  }
  
  public function __call($method, $arg)
  {
    $this->invocation->reset($method, $arg);
    
    $advices = array();
    
    $pointcuts = new Sabel_Aspect_DefaultPointcuts();
    
    foreach ($this->advisor as $advisor) {
      $pointcut = $advisor->getPointcut();
      
      if (!$pointcut instanceof Sabel_Aspect_Pointcut)
        throw new Sabel_Exception_Runtime("pointcut must be Sabel_Aspect_Pointcut");
      
      if ($pointcuts->matches($pointcut, $method, $this->target)) {
        $advice = $advisor->getAdvice();
        
        if (is_array($advice)) {
          $advices = array_merge($advice, $advices);
        } else {
          $advices[] = $advice;
        }
      }
    }
    
    if (count($advices) >= 1) {
      $this->invocation->setAdvices($advices);
    }
    
    return $this->invocation->proceed();
  }
}
