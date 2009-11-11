<?php

class Sabel_Aspect_Weaver
{
  protected $target  = null;
  protected $advisor = array();
  
  public function __construct($target = null)
  {
    if ($target !== null) {
      $this->target = $target;  
    }
  }
  
  public function addAdvisor($advisor, $position = null)
  {
    if ($position === null) {
      $position = count($this->advisor);
    }
    
    $this->advisor[$position] = $advisor;
  }
  
  /**
   * @param object $target
   */
  public function setTarget($target)
  {
    if (class_exists($target)) {
      $this->target = $target;  
    } else {
      throw new Sabel_Exception_Runtime("target must be exist class. {$target} not found");
    }
  }
  
  public function getProxy()
  {
    if ($this->target === null) {
      throw new Sabel_Exception_Runtime("must be set target class");
    }
    
    if (!is_object($this->target)) {
      if (class_exists($this->target, true)) {
        $this->target = new $this->target();
      }
    }
    
    $proxy = new Sabel_Aspect_Proxy_Default($this->target);
    $proxy->__setAdvisor($this->advisor);
    
    return $proxy;
  }
}