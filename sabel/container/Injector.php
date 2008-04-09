<?php

/**
 * Sabel Container
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
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
    $args = null;
    $numberOfArgs = func_num_args();
    
    if ($numberOfArgs > 1) {
      $args = func_get_args();
      array_shift($args);
    }
        
    $reflect = new ReflectionClass($className);
    
    if ($reflect->isInterface() || $reflect->isAbstract()) {
      foreach ($this->injection->getBinds() as $name => $bind) {
        if ($name === $className) {
          $implClassName = $bind->getImplementation();
          return $this->newInstance($implClassName, $args);
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
    } elseif ($args !== null) {
      
      $tmp = array();
      foreach ($args as $arg) {
        if (is_object($arg)) {
          $tmp[] = $arg;
        } elseif (is_string($arg) && class_exists($arg)) {
          $tmp[] = $this->newInstance($arg);
        } else {
          $tmp[] = $arg;
        }
      }
      
      $reflect = new ReflectionClass($className);
      $instance = $reflect->newInstanceArgs($tmp);
    } else {
      $dependencyResolver = new Sabel_Container_DI($this->injection, $this);
      $instance = $dependencyResolver->load($className);
      $instance = $this->applyAspect($instance);
    }
    
    if ($this->injection->hasBinds()) {
      foreach ($this->injection->getBinds() as $name => $binds) {
        foreach ($binds as $bind) {
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
        
        if (is_array($bind)) {
          $implClassName = $bind[0]->getImplementation();  
        } else {
          $implClassName = $bind->getImplementation();  
        }
        
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
