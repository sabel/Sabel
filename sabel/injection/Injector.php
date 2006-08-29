<?php

/**
 * Sabel_Injection_Injector
 * 
 * @package org.sabel.injection
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Injection_Injector
{
  private $target;
  
  public function __construct($target)
  {
    $this->target = $target;
  }
  
  public function __set($key, $value)
  {
    $this->target->$key = $value;
  }
  
  public function __get($key)
  {
    return (isset($this->target->$key)) ? $this->target->$key : null;
  }
  
  public function __call($method, $arg)
  {
    $i = new Sabel_Injection_Calls();
    $i->doBefore($method, $arg);
    
    $result = $this->target->$method($arg);
    
    $i->doAfter($method, $result);
    return $result;
  }
}