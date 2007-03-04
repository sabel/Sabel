<?php

/**
 * customized reflection class. optimized for sabel
 *
 * @package org.sabel.di
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Container_ReflectionClass
{
  protected $reflectionClass = null;
  protected $implementClassName = '';
  protected $dependBy = null;
  
  public function __construct(ReflectionClass $ref, $dependBy = null)
  {
    if (is_object($dependBy)) $this->dependBy = $dependBy;
    $this->reflectionClass = $ref;
    
    if ($ref->isInterface()) {
      $this->implementClassName = $this->getImplementClass();
    }
  }
  
  public function getName()
  {
    return $this->reflectionClass->getName();
  }
  
  public function classType()
  {
    if ($this->reflectionClass->isInterface()) {
      $type = 'interface';
    } elseif ($this->reflectionClass->isAbstract()) {
      $type = 'abstract';
    } elseif ($this->reflectionClass->isInstantiable()) {
      $type = 'class';
    } else {
      $type = 'unknown';
    }
    
    return $type;
  }
  
  public function isInterface()
  {
    return $this->reflectionClass->isInterface();
  }
  
  public function isAbstract()
  {
    return $this->reflectionClass->isAbstract();
  }
  
  public function newInstance($depend = null)
  {
    $className = $this->reflectionClass->getName();
    if ($depend) {
      return new $className($depend);
    } else {
      return new $className();
    }
  }
  
  /**
   * get implementation class name
   *
   * @return string $implementClassname
   */
  public function getImplementClass()
  {
    $interfaceFullName = $this->reflectionClass->getName();
    
    $configClass = "Dependency_Config";
    $conf = new $configClass();
    $confMethod = str_replace("_", "", $interfaceFullName);
    $result = $conf->$confMethod();
    
    $implementClassName = $result->implementation;
        
    if (!is_string($implementClassName)) {
      $information['config']   = $config;
      $information['dependBy'] = $this->dependBy;
      $information['implementClassName'] = $implementClassName;
      throw new SabelException("implement class name is invalid: " . var_export($information, 1));
    }
    
    return $implementClassName;
  }
  
  public function newInstanceForImplementation($dependInstance = null)
  {
    $implementClassName = $this->getImplementClass();

    if ($dependInstance) {
      return new $implementClassName($dependInstance);
    } else {
      return new $implementClassName();
    }
  }
  
  protected function loadConfig($filepath)
  {
    $spyc = new Sabel_Config_Spyc();
    $paths = Sabel_Context::getIncludePath();
    
    foreach ($paths as $path) {
      $fullpath = $path . $filepath;
      if (is_file($fullpath)) return $spyc->load($fullpath);
    }
    return null;
  }
}