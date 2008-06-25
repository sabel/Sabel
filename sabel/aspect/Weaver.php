<?php

class Sabel_Aspect_Weaver
{
  private $target = null;
  
  private $advisor = array();
  
  public function __construct($target)
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
    $proxy = new Sabel_Aspect_DefaultProxy($this->target);
    $proxy->__setAdvisor($this->advisor);
    
    return $proxy;
  }
}