<?php

class ReflectionClassExt
{
  protected $reflectionClass;
  protected $implementClassName;
  
  public function __construct(ReflectionClass $ref)
  {
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
    $pathElements = explode('_', $interfaceFullName);
    $interfaceName = array_pop($pathElements);
    
    $r = ParsedRequest::create();
    $module = $r->getModule();
    
    foreach ($pathElements as $pathelmidx => $pathElement) {
      $pathElements[$pathelmidx] = strtolower($pathElement);
    }
    array_push($pathElements, $interfaceName);
    $configFilePath = implode('/', $pathElements) . '.yml';
    $config = $this->loadConfig($configFilePath);

    if (array_key_exists('module', $config) && array_key_exists($module, $config['module'])) {
      $implementClassName = $config['module'][$module];
    } else {
      if (array_key_exists('implementation', $config)) {
        $implementClassName = $config['implementation'];
      } else {
        throw 
          new SabelException('DI config file is invalid can\' find implementation: ' . $configFilePath);
      }
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
    $paths = array('app/commons/models/', 'app/modules/staff/models/', 'app/modules/user/models/');
    
    foreach ($paths as $pathidx => $path) {
      $fullpath = $path . $filepath;
      if (is_file($fullpath)) break;
    }

    return $spyc->load($fullpath);
  }
}

class SabelDIContainer
{
  public $classStack;
  
  public function loadParameterClass($class, $method = '__construct')
  {
    // push to Stack class name
    $reflectionClass    = new ReflectionClass($class);
    $reflectionClassExt = new ReflectionClassExt($reflectionClass);
    
    if ($reflectionClassExt->isInterface()) {
      $reflectionClass = new ReflectionClass($reflectionClassExt->getImplementClass());
      $this->classStack[] = new ReflectionClassExt($reflectionClass);
      $class = $reflectionClass->getName();
    } else {
      $this->classStack[] = $reflectionClassExt;
    }
    
    if (!$reflectionClass->hasMethod($method)) return false;
    
    // parameter loop
    $refMethod = new ReflectionMethod($class, $method);
    foreach ($refMethod->getParameters() as $paramidx => $parameter) {
      // check parameter required class
      $hasClass = ($dependClass = $parameter->getClass()) ? true : false;
      
      // if parameter required class depend another class
      if ($hasClass) {
        // if it class also depend another class then recursive call
        if ($this->hasParameterDependOnClass($dependClass->getName(), '__construct')) {
          $this->loadParameterClass($dependClass->getName()); // -> call myself
        } else {          
          $this->classStack[] = new ReflectionClassExt($parameter->getClass());
        }
      }
    }
  }
  
  public function loading()
  {
    $stackCount =(int) count($this->classStack);
    
    for ($i = 0; $i < $stackCount; $i++) {
      if ($i == 0) {
        $class = array_pop($this->classStack);
        if ($class->isInterface()) {
          $instance = $class->newInstanceForImplementation();
        } else {
          $instance = $class->newInstance();
        }
      } else {
        $class = array_pop($this->classStack);
        if ($class->isInterface()) {
          $instance = $class->newInstanceForImplementation($instance);
        } else {
          $instance = $class->newInstance($instance);
        }
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
    
    if (count($refMethod->getParameters()) !== 0) {
      return true;
    } else {
      return false;
    }
  }
  
  public static function parseClassDependencyStructure($className, &$structure)
  {
    if (is_null($className)) throw new Exception("class name is null");
    
    $paths = array('app/commons/models/', 'app/modules/staff/models/', 'app/modules/user/models/');
    
    $hasClassPath = (strpos($className, '_') === false) ? true : false;
    
    if ($hasClassPath) {
      $pathElements = explode('_', $className);
      $classFileName = array_pop($pathElements) . '.php';
      $realPathElements = array();
      
      foreach ($pathElements as $peidx => $pathElement) {
        $realPathElements[] = strtolower($pathElement);
      }
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
    
    $structure[$className]['type'] = SabelDIContainer::getClassType($refClass);
    $refMethods = $refClass->getMethods();
    
    foreach ($refMethods as $refMethodsIdx => $refMethod) {
      foreach ($refMethod->getParameters() as $paramidx => $parameter) {
        $isClass = ($paramClassRef = $parameter->getClass()) ? true : false;
        if ($isClass) {
          $paramClassName = $paramClassRef->getName();
          
          // call self recursive process.
          if (!is_null($paramClassName)) {
            self::parseClassDependencyStructure($paramClassName, $structure);
          }
          
          $type = SabelDIContainer::getClassType($paramClassRef);
            
          $structure[$className][$refMethod->getName()][$paramClassName] = 
            array('type'   => $type,
                  'name'   => $paramClassRef->getName(),
                  'define' => $structure[$paramClassName]);
        } else {
          $structure[$className][$refMethod->getName()][$parameter->getName()] = 
            array('type' => 'parameter', 'name' => $parameter->getName());
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

?>