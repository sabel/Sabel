<?php

/**
 * customized reflection class. optimized for sabel
 *
 * @package org.sabel.di
 * @author Mori Reo <mori.reo@servise.com>
 */
class Sabel_Container_ReflectionClass
{
  protected $reflectionClass;
  protected $implementClassName;
  protected $dependBy;
  
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
    } else if ($this->reflectionClass->isAbstract()) {
      $type = 'abstract';
    } else if ($this->reflectionClass->isInstantiable()) {
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
  
  public function newInstance($depend = null)
  {
    $className = $this->reflectionClass->getName();
    if ($depend) {
      return new $className($depend);
    } else {
      return new $className();
    }
  }
  
  public function getImplementClass()
  {
    $interfaceFullName = $this->reflectionClass->getName();
    $pathElements      = explode('_', $interfaceFullName);
    $interfaceName     = array_pop($pathElements);
    
    $module = SabelDIHelper::getModuleName();
    
    foreach ($pathElements as &$pathElement) {
      $pathElement = strtolower($pathElement);
    }
    array_push($pathElements, $interfaceName);
    $configFilePath = implode('/', $pathElements) . '.yml';
    $config = $this->loadConfig($configFilePath);
    
    if (array_key_exists('class', $config) &&
        array_key_exists($this->dependBy->getName(), $config['class'])) {
      $implementClassName = $config['class'][$this->dependBy->getName()];
    } else if (array_key_exists('module', $config) && 
               array_key_exists($module, $config['module'])) {
      $implementClassName = $config['module'][$module];
    } else if (array_key_exists('implementation', $config)) {
      $implementClassName = $config['implementation'];
    } else {
        $msg  = 'DI config file is invalid can\'t find implementation: ';
        $msg .= $configFilePath;
        throw new SabelException($msg);
    }
    
    if (!is_string($implementClassName)) {
      $information['implementClassName'] = $implementClassName;
      $information['config'] = $config;
      $information['dependBy'] = $this->dependBy;
      throw new SabelException("<pre>implement class name is invalid: " . var_export($information, 1));
    }
    
    if (!class_exists($implementClassName)) {
      uses(Sabel_Core_Resolver::resolvClassPathByClassName($implementClassName));
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
    $spyc = new Spyc();
                   
    $paths = Sabel_Core_Context::getIncludePath();
    
    // @todo おかしくね？
    foreach ($paths as $pathidx => $path) {
      $fullpath = $path . $filepath;
      if (is_file($fullpath)) break;
    }

    return $spyc->load($fullpath);
  }
}