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
    
    $argc = count($arg);
    
    if ($argc === 0) {
      $result = $this->target->$method();
    } else if ($argc === 1) {
      $result = $this->target->$method($arg[0]);
    } else if ($argc === 2) {
      $result = $this->target->$method($arg[0], $arg[1]);
    } else if ($argc === 3) {
      $result = $this->target->$method($arg[0], $arg[1], $arg[2]);
    }
    
    $i->doAfter($method, $result);
    return $result;
  }
  
  public function getClassName()
  {
    $ref = new ReflectionClass($this->target);
    return $ref->getName();
  }
}