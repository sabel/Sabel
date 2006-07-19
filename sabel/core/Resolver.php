<?php

/**
 * Sabel_Core_Resolver
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Core_Resolver
{
  protected $classpath;
  public function __construct($classpath)
  {
    $this->classpath = $classpath;
  }
  
  public function resolvClassName()
  {
    $parts = explode('.', $this->classpath);
    foreach ($parts as $pos => $name) {
      $classNames[] = ucfirst($name);
    }
    
    $className = implode('_', $classNames);
    
    
    return $className;
  }
  
  public function resolvRealPath()
  {
    $parts = explode('.', $this->classpath);
    foreach ($parts as $pos => $name) {
      $classNames[] = ucfirst($name);
    }
    $classpath = implode('/', $parts);
  }
}
?>