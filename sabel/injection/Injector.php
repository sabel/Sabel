<?php

class Injection_Manager
{
  protected $reflectionClass = null;
  
  public function __construct($className)
  {
    $this->reflectionClass = $reflectionClass = new ReflectionClass($className);
    
  }
}

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
    
    $annotations = array();
    $ref = new ReflectionClass($this->target);
    foreach ($ref->getProperties() as $property) {
      $annotations = Sabel_Annotation_Reader::getAnnotationsByProperty($property);
    }
    
    if (count($annotations) !== 0) {
      foreach ($annotations as $annotation) {
        if (isset($annotation['implementation'])) {
          $className = $annotation['implementation']->getContents();
          $class = new $className();
          $setter = 'set'. ucfirst($className);
          if (isset($annotation['setter'])) {
            $setter = $annotation['setter']->getContents();
            $this->target->$setter($class);
          } else if ($ref->hasMethod($setter)) {
            $this->target->$setter($class);
          }
        }
      }
    }
    
    $method = $ref->getMethod($method);
    $result = $method->invokeArgs($this->target, $arg);
    
    $i->doAfter($method, $result);
    return $result;
  }
  
  public function getClassName()
  {
    $ref = new ReflectionClass($this->target);
    return $ref->getName();
  }
}