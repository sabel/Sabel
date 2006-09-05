<?php

/**
 * Sabel_Injection_Injector
 * 
 * @package org.sabel.injection
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Injection_Injector
{
  private $container  = null;
  private $target     = null;
  private $reflection = null;
  private $observers  = array();
  
  public function __construct($container, $target)
  {
    if (!is_object($target))
      throw new Sabel_Exception_Runtime("target is not object.");
      
    $this->container  = $container;
    $this->target     = $target;
    $this->reflection = new ReflectionClass($target);
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
    Sabel_Injection_Calls::doBefore($method, $arg);
    $method = $this->reflection->getMethod($method);
    
    $this->notice($method);
    
    $result = $method->invokeArgs($this->target, $arg);
    Sabel_Injection_Calls::doAfter($method, $result);
    return $result;
  }
  
  public function getTarget()
  {
    return $this->target;
  }
  
  public function getReflection()
  {
    return $this->reflection;
  }
  
  public function observe($observer)
  {
    $this->observers[] = $observer;
  }
  
  protected function notice($method)
  {
    $observers = $this->observers;
    foreach ($observers as $observer) $observer->notice($this, $method);
  }
}