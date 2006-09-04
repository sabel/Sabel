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
    $this->loadClass($className, $method);
    return $this->makeInstance();
  }
  
  public function loadInjected($className, $method = '__construct')
  {
    return new Sabel_Injection_Injector($this->load($className, $method));
  }
  
  public function loadClass($class, $method = '__construct')
  {
    // push to Stack class name
    $reflectionClass    = new ReflectionClass($class);
    $reflectionClassExt = new Sabel_Container_ReflectionClass($reflectionClass, $reflectionClass);
    
    if (!$reflectionClass->hasMethod($method)) return false;
    
    if ($reflectionClassExt->isInterface()) {
      $reflectionClass =
        new ReflectionClass($reflectionClassExt->getImplementClass());
        
      $this->classStack[] = new Sabel_Container_ReflectionClass($reflectionClass);
      $class = $reflectionClass->getName();
    } else {
      $this->classStack[] = $reflectionClassExt;
    }
    
    // parameters loop
    $refMethod = $reflectionClass->getMethod($method);
    foreach ($refMethod->getParameters() as $param) {
      // check parameter required class
      $hasClass = ($dependClass = $param->getClass()) ? true : false;
      
      // if parameter required class depend another class.
      if ($hasClass) {
        // if it class also depend another class then recursive call.
        $depend = $dependClass->getName();
        if ($this->hasParameterDependOnClass($depend, '__construct')) {
          $this->loadClass($depend);
        } else {
          $this->classStack[] = new Sabel_Container_ReflectionClass($param->getClass(), $reflectionClass);
        }
      }
    }
    
    return $this;
  }
  
  public function makeInstance()
  {
    $stackCount = (int) count($this->classStack);
    
    if ($stackCount < 0) {
      throw new SabelException('invalid stack count:' . var_export($this->classStack, 1));
    }
    
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
  
  public function hasParameterDependOnClass($class, $method)
  {
    $refClass  = new ReflectionClass($class);
    
    if (self::getClassType($refClass) === 'interface') {
      return false;
    } else {
      $refMethod = new ReflectionMethod($class, $method);
    }
    
    return (count($refMethod->getParameters() !== 0));
  }
  
  public static function parseClassDependencyStruct($className, &$structure)
  {
    if (is_null($className)) throw new Exception("class name is null");
    
    $paths = SabelContext::getIncludePath();
    
    $hasClassPath = (strpos($className, '_') === false) ? true : false;
    
    if ($hasClassPath) {
      $pathElements = explode('_', $className);
      $classFileName = array_pop($pathElements) . '.php';

      $realPathElements = array_map('strtolower', $pathElement);
      $realPathElements[] = $classFileName;
      
      $realPath = implode('/', $realPathElements);
      
      foreach ($paths as $pathidx => $path) {
        if (!class_exists($className)) {
          require_once($path . $realPath);
          break;
        }
      }
    } else {
      foreach ($paths as $pathidx => $path) {
        if (!class_exists($className)) {
          if (is_file($path . $className . '.php')) {
            require_once($path . $className . '.php');
            break;
          }
        }
      }
    }
    
    $refClass = new ReflectionClass($className);
    
    $structure[$className]['type'] =
      SabelDIContainer::getClassType($refClass);
      
    $refMethods = $refClass->getMethods();
    
    foreach ($refMethods as $refMethodsIdx => $refMethod) {
      foreach ($refMethod->getParameters() as $paramidx => $parameter) {
        $isClass = ($paramClassRef = $parameter->getClass()) ? true : false;
        if ($isClass) {
          $paramClassName = $paramClassRef->getName();
          
          // call self recursive process.
          if (!is_null($paramClassName)) {
            self::parseClassDependencyStruct($paramClassName, $structure);
          }
          
          $type = SabelDIContainer::getClassType($paramClassRef);
            
          $structure[$className][$refMethod->getName()][$paramClassName] =
            array('type'   => $type,
                  'name'   => $paramClassRef->getName(),
                  'define' => $structure[$paramClassName]);
        } else {
          $structure[$className][$refMethod->getName()][$parameter->getName()]
            = array('type' => 'parameter', 'name' => $parameter->getName());
        }
      }
    }
  }
  
  public static function getClassType($reflectionClass)
  {
    if ($reflectionClass->isInterface()) {
      $type = 'interface';
    } else if ($reflectionClass->isAbstract()) {
      $type = 'abstract';
    } else if ($reflectionClass->isInstantiable()) {
      $type = 'class';
    } else {
      $type = 'unknown';
    }
    
    return $type;
  }
}
