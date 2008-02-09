<?php

/**
 * Sabel Container
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Container_Injector
{
  private $injection = null;
  
  /**
   * default constructer
   *
   * @param Sabel_Container_Injection $injection
   */
  public function __construct($injection)
  {
    if (!$injection instanceof Sabel_Container_Injection) {
      throw new Sabel_Exception_Runtime("must be Sabel_Container_Injection");
    }
    
    $this->injection = $injection;
    $this->injection->configure();
  }
  
  /**
   * get new class instance from class name
   *
   * @param string $className
   * @return object
   */
  public function newInstance($className)
  {
    $reflect = new ReflectionClass($className);
    
    if ($reflect->isInterface() || $reflect->isAbstract()) {
      foreach ($this->injection->getBinds() as $name => $bind) {
        if ($name === $className) {
          $implClassName = $bind->getImplementation();
          return $this->newInstance($implClassName);
        }
      }
    }
    
    if ($this->injection->hasConstruct($className)) {
      $construct = $this->injection->getConstruct($className);
      $constructArguments = array();
      
      foreach ($construct->getConstructs() as $constructValue) {
        if ($this->exists($constructValue)) {
          $instance = $this->constructInstance($constructValue);
          $constructArguments[] = $this->applyAspect($instance);
        } else {
          $constructArguments[] = $constructValue;
        }
      }
      
      $reflect = new ReflectionClass($className);
      $instance = $reflect->newInstanceArgs($constructArguments);
    } else {
      $dependencyResolver = new Sabel_Container_DI();
      $instance = $dependencyResolver->load($className);
      $instance = $this->applyAspect($instance);
    }
    
    foreach ($this->injection->getBinds() as $name => $bind) {
      if ($bind->hasSetter()) {
        $injectionMethod = $bind->getSetter();
      } else {
        $injectionMethod = "set" . ucfirst($name);
      }
      $implClassName = $bind->getImplementation();
      $reflect = new ReflectionClass($instance);
      if ($reflect->hasMethod($injectionMethod)) {
        $instance->$injectionMethod($this->newInstance($implClassName));
      }
    }
    
    return $instance;
  }
  
  private final function applyAspect($instance)
  {
    $reflect = new ReflectionClass($instance);
    $className = $reflect->getName();
    
    if ($this->injection->hasAspect($className)) {
      $aspect = $this->injection->getAspect($className);
      foreach ($aspect->getAppliedAspects() as $appliedAspect) {
        $pointcut = Sabel_Aspect_Pointcut::create($appliedAspect);
        foreach ($aspect->getAppliedMethods() as $method) {
          $pointcut->addMethod($method);
        }
        Sabel_Aspect_Aspects::singleton()->addPointcut($pointcut);
      }
      return new Sabel_Aspect_Proxy($instance);
    } else {
      return $instance;
    }
  }
  
  private final function constructInstance($dependClassName)
  {
    $reflect = new ReflectionClass($dependClassName);
    if ($reflect->isInterface()) {
      if ($this->injection->hasBind($dependClassName)) {
        $bind = $this->injection->getBind($dependClassName);
        $implClassName = $bind->getImplementation();
        return $this->newInstance($implClassName);
      }
    } else {
      return $this->newInstance($dependClassName);
    }
  }
  
  private final function exists($className)
  {
    return (class_exists($className) || interface_exists($className));
  }
}
