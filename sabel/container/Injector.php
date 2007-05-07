<?php

/**
 * Sabel Container
 *
 * @category   container
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
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
    if ($this->injection->hasConstruct($className)) {
      $construct = $this->injection->getConstruct($className);
      $constructArguments = array();
      
      foreach ($construct->getConstructs() as $constructValue) {
        if ($this->exists($constructValue)) {
          $constructArguments[] = $this->constructInstance($constructValue);
        } else {
          $constructArguments[] = $constructValue;
        }
      }
      
      $reflect = new ReflectionClass($className);
      $instance = $reflect->newInstanceArgs($constructArguments);
    } else {
      $dependencyResolver = new Sabel_Container_DI();
      $instance = $dependencyResolver->load($className);
    }
    
    foreach ($this->injection->getBinds() as $name => $bind) {
      $injectionMethod = "set" . ucfirst($name);
      $implClassName = $bind->getImplementation();
      $reflect = new ReflectionClass($instance);
      if ($reflect->hasMethod($injectionMethod)) {
        $dependencyResolver = new Sabel_Container_DI();
        $instance->$injectionMethod($dependencyResolver->load($implClassName));
      }
    }
    
    return $instance;
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
