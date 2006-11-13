<?php

/**
 * Sabel DI Container
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Container_DI
{
  public $classStack = array();
  
  public static function create()
  {
    return new self();
  }
  
  /**
   * load instance of $className;
   *
   * @return Object instance
   */
  public function load($className, $method = '__construct')
  {
    if ($this->loadClass($className, $method)) return $this->makeInstance();
  }
  
  public function loadInjected($className, $method = '__construct')
  {
    return new Sabel_Aspect_Proxy($this->load($className, $method));
  }
  
  public function loadClass($class, $method = '__construct')
  {
    // push to Stack class name
    $reflectionClass    = new ReflectionClass($class);
    $reflectionClassExt = new Sabel_Container_ReflectionClass($reflectionClass, $reflectionClass);
    
    if (!$reflectionClass->hasMethod($method)) return false;
    
    if ($reflectionClass->isInterface()) {
      $reflectionClass    = new ReflectionClass($reflectionClassExt->getImplementClass());
      $reflectionClassExt = new Sabel_Container_ReflectionClass($reflectionClass);
    }
    $this->classStack[] = $reflectionClassExt;
    
    // parameters loop
    $refMethod = $reflectionClass->getMethod($method);
    foreach ($refMethod->getParameters() as $param) {
      // check parameter required class
      if ($dependClass = $param->getClass()) {
        // if it class also depend another class then recursive call.
        $depend = $dependClass->getName();
        if ($this->hasParameterDependOnClass($depend)) {
          $this->loadClass($depend);
        } else {
          $this->classStack[] = new Sabel_Container_ReflectionClass($dependClass, $reflectionClass);
        }
      }
    }
    
    return $this;
  }
  
  public function makeInstance()
  {
    $stackCount =(int) count($this->classStack);
    
    if ($stackCount < 1)
      throw new SabelException('invalid stack count:' . var_export($this->classStack, 1));
      
    $class = array_pop($this->classStack);
    if (is_null($class)) throw new Sabel_Exception_Runtime("class is null.");
    
    if ($class->isInterface()) {
      $instance = $class->newInstanceForImplementation();
    } else {
      $instance = $class->newInstance();
    }
    
    for ($i = 1; $i < $stackCount; $i++) {
      $class = array_pop($this->classStack);
      if ($class->isInterface()) {
        $instance = $class->newInstanceForImplementation($instance);
      } else {
        $instance = $class->newInstance($instance);
      }
    }
    
    return $instance;
  }
  
  public function hasParameterDependOnClass($class, $method = '__construct')
  {
    $refClass  = new ReflectionClass($class);
    
    if (self::getClassType($refClass) === 'interface') {
      return false;
    } else {
      $refMethod = new ReflectionMethod($class, $method);
    }
    
    return (count($refMethod->getParameters() !== 0));
  }
  
  public static function getClassType($reflectionClass)
  {
    if ($reflectionClass->isInterface()) {
      $type = 'interface';
    } elseif ($reflectionClass->isAbstract()) {
      $type = 'abstract';
    } elseif ($reflectionClass->isInstantiable()) {
      $type = 'class';
    } else {
      $type = 'unknown';
    }
    
    return $type;
  }
}
