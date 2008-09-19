<?php

class Sabel_Aspect_Weaver_Dynamic extends Sabel_Aspect_Weaver_Abstract
{
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