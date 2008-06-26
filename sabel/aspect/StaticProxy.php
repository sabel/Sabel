<?php

class Sabel_Aspect_StaticProxy extends Sabel_Aspect_AbstractProxy
{
  protected function setupInvocation()
  {
    $this->invocation = new Sabel_Aspect_DefaultMethodInvocation($this, $this->target);
  }
  
  public function __call($method, $arg)
  {
    $this->invocation->reset($method, $arg);
    
    return $this->invocation->proceed();
  }
  
  public function getClassName()
  {
    return get_class($this->target);
  }
}