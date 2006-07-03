<?php

class SabelDIHelper
{
  public static function getModuleName()
  {
    if (class_exists('ParsedRequest')) {
      return ParsedRequest::create()->getModule();
    } else {
      return 'module';
    }
  }
}

interface InjectionCall
{
  public function executeBefore($method, $arg);
  public function executeAfter($method, $result);
}

class InjectionCalls
{
  private static $before = array();
  private static $after  = array();
  
  /**
   * add both before and after injection.
   *
   * @param InjectionCall object
   * @return void
   */
  public function addBoth($injection)
  {
    if ($injection instanceOf InjectionCall) {
      $this->addBefore($injection);
      $this->addAfter($injection);
    } else {
      throw new SabelException(var_export($injection, 1) . ' is not InjectionCall object');
    }
  }
  
  public function doBefore($method, $arg)
  {
    foreach (self::$before as $bi => $object) {
      $object->executeBefore($method, $arg);
    }
  }
  
  public function doAfter($method, $result)
  {
    foreach (self::$after as $ai => $object) {
      $object->executeAfter($method, $result);
    }
  }
  
  public function addBefore($injection)
  {
    self::$before[] = $injection;
  }
  
  public function addAfter($injection)
  {
    self::$after[] = $injection;
  }
}

class Injector
{
  private $target;
  
  public function __construct($target)
  {
    $this->target = $target;
  }
  
  public function __call($method, $arg)
  {
    
    $i = new InjectionCalls();
    $i->doBefore($method, $arg);
    
    $result = $this->target->$method($arg);
    
    $i->doAfter($method, $result);
    
    return $result;
  }
}

class SabelReflectionClass
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
    $pathElements = explode('_', $interfaceFullName);
    $interfaceName = array_pop($pathElements);
    
    $module = SabelDIHelper::getModuleName();
    
    foreach ($pathElements as $pathelmidx => $pathElement) {
      $pathElements[$pathelmidx] = strtolower($pathElement);
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
    
    if (!class_exists($implementClassName)) uses(convertClassPath($implementClassName));
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
                   
    $paths = SabelContext::getIncludePath();
    
    foreach ($paths as $pathidx => $path) {
      $fullpath = $path . $filepath;
      if (is_file($fullpath)) break;
    }

    return $spyc->load($fullpath);
  }
}

/**
 * Sabel DI Container
 *
 * @author Mori Reo <mori.reo@servise.jp
 */
class SabelDIContainer
{
  public $classStack;
  
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
    $this->loadClass($className, $method);
    return new Injector($this->makeInstance());
  }
  
  public function loadClass($class, $method)
  {
    if (!class_exists($class)) uses(convertClassPath($class));
    
    // push to Stack class name
    $reflectionClass    = new ReflectionClass($class);
    $reflectionClassExt = new SabelReflectionClass($reflectionClass, $reflectionClass);
    
    if ($reflectionClassExt->isInterface()) {
      $reflectionClass = 
        new ReflectionClass($reflectionClassExt->getImplementClass());
        
      $this->classStack[] = new SabelReflectionClass($reflectionClass);
      $class = $reflectionClass->getName();
    } else {
      $this->classStack[] = $reflectionClassExt;
    }
    
    if (!$reflectionClass->hasMethod($method)) return false;
    
    // parameters loop
    $refMethod = new ReflectionMethod($class, $method);
    foreach ($refMethod->getParameters() as $paramidx => $param) {
      // check parameter required class
      $hasClass = ($dependClass = $param->getClass()) ? true : false;
      
      // if parameter required class depend another class
      if ($hasClass) {
        // if it class also depend another class then recursive call
        $depend = $dependClass->getName();
        if ($this->hasParameterDependOnClass($depend, '__construct')) {
          $this->loadClass($dependClass->getName()); // call myself
        } else {          
          $this->classStack[] = new SabelReflectionClass($param->getClass(), $reflectionClass);
        }
      }
    }
    
    return $this;
  }
  
  public function makeInstance()
  {
    $stackCount =(int) count($this->classStack);
    
    if ($stackCount < 0) {
      throw new SabelException('invalid stack count:' . var_export($this->classStack, 1));
    }
    
    $class = array_pop($this->classStack);
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
    
    if (count($refMethod->getParameters()) !== 0) {
      return true;
    } else {
      return false;
    }
  }
  
  public static function parseClassDependencyStruct($className, &$structure)
  {
    if (is_null($className)) throw new Exception("class name is null");
    
    // @todo this array will configurable.
    $paths = array('app/commons/models/',
                   'app/modules/staff/models/', 
                   'app/modules/user/models/');
    
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

?>