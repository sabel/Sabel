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
  /**
   * @var Sabel_Container_Injection
   */
  protected $injection = null;
  
  /**
   * @var array of dependency
   */
  protected $dependency = array();
  
  /**
   * @vararray reflection cache
   */
  protected $reflectionCache = array();
  
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
    $injection->configure();
  }
  
  private $reflection = null;
  
  /**
   * get new class instance from class name
   *
   * @param string $className
   * @return object
   */
  public function newInstance($className)
  {
    $reflect = $this->getReflection($className);
    
    if ($reflect->isInterface() || $reflect->isAbstract()) {
      $binds = $this->injection->getBind($className);
      $bind  = (is_array($binds)) ? $binds[0] : $binds;
      
      $implementation = $bind->getImplementation();
      
      if ($this->injection->hasConstruct($className)) {
        $instance = $this->newInstanceWithConstructInAbstract($reflect, $className, $implementation);
      } else {
        $instance = $this->newInstance($implementation);
      }
    } else {
      $instance = $this->newInstanceWithConstruct($reflect, $className);
    }
    
    return $this->applyAspect($this->injectToSetter($reflect, $instance));
  }
  
  protected function injectToSetter($reflect, $instance)
  {
    if (!$this->injection->hasBinds()) return $instance;
    
    foreach ($this->injection->getBinds() as $name => $binds) {
      foreach ($binds as $bind) {
        if ($bind->hasSetter()) {
          $injectionMethod = $bind->getSetter();
        } else {
          $injectionMethod = "set" . ucfirst($name);
        }
        $implClassName = $bind->getImplementation();
        
        if (in_array($injectionMethod, get_class_methods($instance))) {
          $argumentInstance = $this->newInstanceWithConstruct($reflect, $implClassName);
          $instance->$injectionMethod($argumentInstance);
        }
      }
    }
    
    return $instance;
  }
  
  protected function newInstanceWithConstruct($reflect, $className)
  {
    if ($this->injection->hasConstruct($reflect->getName())) {
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
      
      $instance = $reflect->newInstanceArgs($constructArguments);
    } else {
      $instance = $this->newInstanceWithConstructDependency($className);
    }
    
    return $instance;
  }
  
  protected function newInstanceWithConstructInAbstract($reflect, $className, $implClass)
  {
    if ($this->injection->hasConstruct($className)) {
      $construct = $this->injection->getConstruct($className);
      $constructArguments = array();
      
      foreach ($construct->getConstructs() as $constructValue) {
        if ($this->exists($constructValue)) {
          // @todo test this condition
          $instance = $this->constructInstance($constructValue);
          $constructArguments[] = $this->applyAspect($instance);
        } else {
          $constructArguments[] = $constructValue;
        }
      }
      
      $reflect  = $this->getReflection($implClass);
      $instance = $this->applyAspect($reflect->newInstanceArgs($constructArguments));
      
      return $instance;
    } else {
      return $this->applyAspect($this->newInstanceWithConstructDependency($className));
    }
  }
  
  protected function applyAspect($instance)
  {
    if ($instance === null) {
      throw new Sabel_Exception_Runtime("invalid instance " . var_export($instance, 1));
    }
    
    $className = get_class($instance);
    
    if (!$this->injection->hasAspect($className)) return $instance;
    
    $aspect = $this->injection->getAspect($className);
    
    foreach ($aspect->getAspects() as $appliedAspect) {
      $pointcut = Sabel_Aspect_Pointcut::create($appliedAspect);
      foreach ($aspect->getMethods() as $method) {
        $pointcut->addMethod($method);
      }
      Sabel_Aspect_Aspects::singleton()->addPointcut($pointcut);
    }
    
    return new Sabel_Aspect_Proxy($instance);
  }
  
  /**
   * load instance of $className;
   *
   * @return object constructed instance
   */
  protected function newInstanceWithConstructDependency($className)
  {
    $this->scanDependency($className);
    $instance = $this->buildInstance();
    unset($this->dependency);
    $this->dependency = array();
    
    return $this->applyAspect($instance);
  }
  
  protected function constructInstance($className)
  {
    $reflect = $this->getReflection($className);
    
    if ($reflect->isInterface()) {
      if ($this->injection->hasBind($className)) {
        $bind = $this->injection->getBind($className);
        
        if (is_array($bind)) {
          $implement = $bind[0]->getImplementation();  
        } else {
          $implement = $bind->getImplementation();  
        }
        
        return $this->newInstance($implement);
      } else {
        throw new Sabel_Exception_Runtime("any '{$className}' implementation can't find");
      }
    } else {
      return $this->newInstance($className);
    }
  }
  
  protected function exists($className)
  {
    return (class_exists($className) || interface_exists($className));
  }
  
  /**
   * scan dependency
   * 
   * @todo cycric dependency
   * @param string $class class name
   * @throws Sabel_Exception_Runtime when class does not exists
   */
  protected function scanDependency($className)
  {
    $constructerMethod = "__construct";
    
    if (!class_exists($className)) {
      throw new Sabel_Exception_Runtime("{$className} does't exist");
    }
    
    $reflection = $this->getReflection($className);
    
    $this->dependency[] = $reflection;
    
    if (!$reflection->hasMethod($constructerMethod)) return $this;
    
    foreach ($reflection->getMethod($constructerMethod)->getParameters() as $parameter) {
      if (!$parameter->getClass()) continue;
      
      $dependClass = $parameter->getClass()->getName();
      
      if ($this->hasMoreDependency($dependClass)) {
        $this->scanDependency($dependClass);
      } else {
        $this->dependency[] = $this->getReflection($dependClass);
      }
    }
    
    return $this;
  }
  
  /**
   * @param string $class class name
   */
  protected function hasMoreDependency($class)
  {
    $constructerMethod = "__construct";
    
    $reflection = $this->getReflection($class);
    
    if ($reflection->isInterface() || $reflection->isAbstract()) return false;
    
    if ($reflection->hasMethod($constructerMethod)) {
      $refMethod = new ReflectionMethod($class, $constructerMethod);
      return (count($refMethod->getParameters()) !== 0);
    } else {
      return false;
    }
  }
  
  /**
   * construct an all depended classes
   *
   * @return object
   */
  protected function buildInstance()
  {
    $stackCount =(int) count($this->dependency);
    if ($stackCount < 1) {
      $msg = "invalid stack count";
      throw new Sabel_Exception_Runtime($msg);
    }
    
    $instance = null;
    
    for ($i = 0; $i < $stackCount; ++$i) {
      $reflection = array_pop($this->dependency);
      if ($reflection !== null) {
        $className = $reflection->getName();
        
        if ($this->injection->hasConstruct($className)) {
          $instance = $this->newInstance($className);
        } else {
          if ($reflection->isInstanciatable()) {
            $instance = $this->getInstance($className, $instance);
          } else {
            $instance = $this->newInstance($className);
          }
        }
      }
    }
    
    return $instance;
  }
  
  /**
   * get instance of class name
   */
  protected function getInstance($className, $instance = null)
  {
    if (!class_exists($className)) {
      throw new Sabel_Exception_Runtime("class doesn't exists");
    }
    
    if ($instance === null) {
      return new $className();
    } else {
      return new $className($instance);
    }
  }
  
  /**
   * get reflection class
   *
   */
  protected function getReflection($className)
  {
    if (!isset($this->reflectionCache[$className])) {
      $reflection = new Sabel_Reflection_Class($className);
      $this->reflectionCache[$className] = $reflection;
      return $reflection;
    }
    
    return $this->reflectionCache[$className];
  }
}
