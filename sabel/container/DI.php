<?php

/**
 * Sabel DI Container
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Container_DI
{
  protected $classStack = array();
  
  protected $depends = array();
  
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
    $this->parseDependency($className, $method);
    $instance = $this->constructInstance();
    return $instance;
  }
  
  public function depends($className, $dependsClassName, $type)
  {
    $this->depends[$className][] = array($dependsClassName, $type);
  }
  
  public function loadInjected($className, $method = '__construct')
  {
    return new Sabel_Aspect_Proxy($this->load($className, $method));
  }
  
  public function parseDependency($class, $method = '__construct')
  {
    // push to Stack class name
    $reflectionClass    = new ReflectionClass($class);
    $reflectionClassExt = new Sabel_Container_ReflectionClass($reflectionClass, $reflectionClass);
    
    if ($reflectionClass->isInterface() || $reflectionClass->isAbstract()) {
      $reflectionClass    = new ReflectionClass($reflectionClassExt->getImplementClass());
      $reflectionClassExt = new Sabel_Container_ReflectionClass($reflectionClass);
    }
    
    $this->classStack[] = $reflectionClassExt;
    
    if ($reflectionClass->hasMethod($method)) {
      // parameters loop
      $refMethod = $reflectionClass->getMethod($method);
      foreach ($refMethod->getParameters() as $param) {
        // check parameter required class
        if ($dependClass = $param->getClass()) {
          // recursive call if class also depend another class.
          $depend = $dependClass->getName();
          if ($this->hasParameterDependOnClass($depend)) {
            $this->parseDependency($depend);
          } else {
            $this->classStack[] = new Sabel_Container_ReflectionClass($dependClass, $reflectionClass);
          }
        }
      }
    }
    
    return $this;
  }
  
  public function constructInstance()
  {
    $stackCount =(int) count($this->classStack);
    
    if ($stackCount < 1) {
      throw new SabelException('invalid stack count:' . var_export($this->classStack, 1));
    }
    
    $class = array_pop($this->classStack);
    if ($class === null) throw new Sabel_Exception_Runtime("class is null.");
    
    if ($class->isInterface()) {
      $instance = $class->newInstanceForImplementation();
    } elseif ($class->isAbstract()) {
      $instance = $class->newInstanceForImplementation();
    } else {
      $instance = $class->newInstance();
    }
    
    for ($i = 1; $i < $stackCount; ++$i) {
      $class = array_pop($this->classStack);
      
      if ($class->isInterface()) {
        $instance = $class->newInstanceForImplementation($instance);
      } elseif ($class->isAbstract()) {
        $instance = $class->newInstanceForImplementation($instance);
      } else {
        $instance = $class->newInstance($instance);
      }
      
      $this->loadDependsClass($class->getName(), $instance);
    }
    
    return $instance;
  }
  
  protected function loadDependsClass($className, $instance)
  {
    if (!isset($this->depends[$className])) return;
    
    foreach ($this->depends[$className] as $value) {
      $className = $value[0];
      $type      = $value[1];
      
      if (class_exists($className)) {
        switch ($type) {
          case "Setter":
            $setter = "set".ucfirst($className);
            $instance->$setter(self::create()->loadClass($className)->makeInstance());
            break;
        }
      }
    }
  }
  
  public function hasParameterDependOnClass($class, $method = '__construct')
  {
    $refClass  = new ReflectionClass($class);
    $result = null;
    
    if (self::getClassType($refClass) === 'interface') {
      $result = false;
    } elseif ($refClass->hasMethod($method)) {
      $refMethod = new ReflectionMethod($class, $method);
      $result = (count($refMethod->getParameters() !== 0));
    }
    
    return $result;
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