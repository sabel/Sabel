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
    $interfaceName     = array_pop($pathElements) . '.yml';
    
    $module = SabelDIHelper::getModuleName();
    
    $pathElements = array_map('strtolower', $pathElements);
    $pathElements[] = $interfaceName;
    $configFilePath = implode('/', $pathElements);
    $config = $this->loadConfig($configFilePath);
    
    if (isset($config['class']) &&
        isset($config['class'][$this->dependBy->getName()])) {
      $implementClassName = $config['class'][$this->dependBy->getName()];
    } elseif (isset($config['module']) &&
               isset($config['module'][$module])) {
      $implementClassName = $config['module'][$module];
    } elseif (isset($config['implementation'])) {
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
    $spyc = new Sabel_Config_Spyc();
    $paths = Sabel_Core_Context::getIncludePath();
    
    foreach ($paths as $path) {
      $fullpath = $path . $filepath;
      if (is_file($fullpath)) return $spyc->load($fullpath);
    }
    return null;
  }
}
