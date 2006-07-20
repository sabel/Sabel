<?php

/**
 * class injection wrapper.
 *
 * @package org.sabel.aop
 * @author Mori Reo <mori.reo@servise.jp>
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
    return $this->target->$key;
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