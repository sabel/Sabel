<?php

class Sabel_Aspect_StaticProxy extends Sabel_Aspect_AbstractProxy
{
  protected function setupInvocation()
  {
    $this->invocation = new Sabel_Aspect_DefaultMethodInvocation($this, $this->target);
  }
  
  private $adviced = null;
  
  public function setAdviced($adviced)
  {
    $this->adviced = $adviced;
  }
  
  public function __call($method, $arg)
  {
    $this->invocation->reset($method, $arg);
    
    if ($this->adviced->hasAdvice($method)) {
      $this->invocation->setAdvices($this->adviced->getAdvice($method));
    }
    
    return $this->invocation->proceed();
  }
}