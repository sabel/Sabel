<?php

abstract class Sabel_Aspect_Proxy_Abstract
{
  protected $target = null;
  
  protected $advisor = array();
  
  protected $invocation = null;
  
  public function __construct($targetObject)
  {
    $this->target = $targetObject;
    $this->__setupInvocation();
    
    if (!$this->invocation instanceof Sabel_Aspect_MethodInvocation) {
      throw new Sabel_Exception_Runtime("invocation must be setup");
    }
  }
  
  abstract protected function __setupInvocation();
  
  public function __getTarget()
  {
    return $this->target;
  }
  
  public function __setAdvisor($advisor)
  {
    $this->advisor = $advisor;
  }
  
  public function getClassName()
  {
    return get_class($this->target);
  }
}