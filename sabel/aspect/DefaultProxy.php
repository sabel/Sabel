<?php

class Sabel_Aspect_DefaultProxy
{
  private $target = null;
  
  private $advisor = array();
  
  private $invocation = null;
  
  public function __construct($targetObject)
  {
    $this->target = $targetObject;
    $this->invocation = new Sabel_Aspect_DefaultMethodInvocation($this, $this->target);
  }
  
  public function __getTarget()
  {
    return $this->target;
  }
  
  public function __setAdvisor($advisor)
  {
    $this->advisor = $advisor;
  }
  
  public function __call($method, $arg)
  {
    $this->invocation->reset();
    $this->invocation->setMethod($method);
    $this->invocation->setArgument($arg);
    
    $match = false;
    
    $advices = array();
    
    foreach ($this->advisor as $advisor) {
      $pointcut = $advisor->getPointcut();
      
      if (!$pointcut instanceof Sabel_Aspect_Pointcut) continue;
      
      $pointcuts = new DefaultPointcuts();
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
}