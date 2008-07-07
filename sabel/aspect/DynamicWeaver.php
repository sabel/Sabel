<?php

class Sabel_Aspect_DynamicWeaver extends Sabel_Aspect_AbstractWeaver
{
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