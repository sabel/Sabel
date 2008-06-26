<?php

class Sabel_Aspect_DynamicWeaver implements Sabel_Aspect_Weaver
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
    
    $proxy = new Sabel_Aspect_DefaultProxy($this->target);
    $proxy->__setAdvisor($this->advisor);
    
    return $proxy;
  }
}