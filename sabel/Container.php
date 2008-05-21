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
class Sabel_Container
{
  private static $configs = array();
  
  /**
   *
   * @param mixed $config object | string
   */
  public static function create($config)
  {
    if (is_object($config) && $config instanceof Sabel_Container_Injection) {
      return new self($config);
    } elseif (is_string($config)) {
      if (isset(self::$configs[$config])) {
        return new self(self::$configs[$config]);
      } elseif (isset(self::$configs["default"])) {
        return new self(self::$configs["default"]);
      } else {
        throw new Sabel_Container_Exception_InvalidConfiguration("{$config} not registered");
      }
    } else {
      throw new Sabel_Container_Exception_InvalidConfiguration();
    }
  }
  
  /**
   * create new instance with injection config
   *
   * @param string $className
   * @param mixed $config object | string
   */
  public static function load($className, $config = null)
  {
    if (is_object($config) && $config instanceof Sabel_Container_Injection) {
      return self::create($config)->newInstance($className);
    } elseif (is_string($config)) {
      if (self::hasConfig($config)) {
        return self::create($config)->newInstance($className);
      } else {
        return self::load($className, "default");
      }
    } elseif ($config === null) {
      $config = "default";
      return self::load($className, $config);
    } else {
      throw new Sabel_Container_Exception_InvalidConfiguration("configuration not found");
    }
  }
  
  /**
   * addConfig 
   * 
   * @param string $name 
   * @param Sabel_Container_Injection $config
   * @static
   * @access public
   * @return void
   * @throws Sabel_Container_Exception_InvalidConfiguration
   */
  public static function addConfig($name, Sabel_Container_Injection $config)
  {
    if (!$config instanceof Sabel_Container_Injection) {
      $msg = "object type must be Sabel_Container_Injection";
      throw new Sabel_Container_Exception_InvalidConfiguration($msg);
    }
    
    if (isset(self::$configs[$name])) {
      throw new Sabel_Container_Exception_InvalidConfiguration("duplicate {$name} entry");
    } else {
      self::$configs[$name] = $config;
    }
    
    if (!isset(self::$configs[$name])) {
      $msg = "unknown exception";
      throw new Sabel_Container_Exception_InvalidConfiguration($msg);
    }
  }
  
  public static function hasConfig($name)
  {
    return self::$configs[$name];
  }
  
  public static function getConfig($name)
  {
    if (self::hasConfig($name)) {
      return self::$configs[$name];
    } else {
      throw new Sabel_Container_Exception_InvalidConfiguration("{$config} not registered");
    }
  }
  
  /**
   * clearConfig 
   * 
   * @param string $name 
   * @static
   * @access public
   * @return boolean
   */
  public static function clearConfig($name)
  {
    if (!is_string($name)) {
      throw new Sabel_Exception_Runtiem("name must be string givin: " . var_export($name, true));
    }
    
    if (self::hasConfig($name)) {
      unset(self::$configs[$name]);
      return true;
    } else {
      return false;
    }
  }
  
  public static function clearAllConfigs()
  {
    self::$configs = array();
  }
  
  /**
   * @var Sabel_Container_Injection
   */
  protected $config = null;
  
  /**
   * @var array reflection cache
   */
  protected $reflection = null;
  
  /**
   * @var array of dependency
   */
  protected $dependency = array();
  
  /**
   * @var array reflection cache
   */
  protected $reflectionCache = array();
  
  /**
   * default constructer
   *
   * @param Sabel_Container_Injection $injection
   */
  public function __construct($config)
  {
    if (!$config instanceof Sabel_Container_Injection) {
      $msg = "object type must be Sabel_Container_Injection";
      throw new Sabel_Container_Exception_InvalidConfiguration($msg);
    }
    
    $config->configure();
    $this->config = $config;
  }
  
  /**
   * get new class instance from class name
   *
   * @param string $className
   * @return object
   */
  public function newInstance($className, $arguments = null)
  {
    $reflection = $this->getReflection($className);
    
    if ($reflection->isInstanciatable()) {
      if (is_array($arguments)) {
        $instance = $reflection->newInstanceArgs($constructArguments);
      } elseif (is_string($arguments)) {
        $instance = $reflection->newInstance($arguments);
      } else {
        $instance = $this->newInstanceWithConstruct($reflection, $className);
      }
    } else {
      $binds = $this->config->getBind($className);
      $bind  = (is_array($binds)) ? $binds[0] : $binds;
      
      $implementation = $bind->getImplementation();
      
      if ($this->config->hasConstruct($className)) {
        $instance = $this->newInstanceWithConstructInAbstract($className, $implementation);
      } elseif (is_array($arguments)) {
        $instance = $reflection->newInstanceArgs($constructArguments);
      } elseif (is_string($arguments)) {
        $instance = $reflection->newInstance($arguments);
      } else {
        $instance = $this->newInstance($implementation);
      }
    }
    
    return $this->applyAspect($this->injectToSetter($reflection, $instance));
  }
  
  /**
   * inject to setter
   *
   * @param Sabel_Reflection_Class $reflection
   * @param Object $instance
   */
  protected function injectToSetter($reflection, $instance)
  {
    if (!$this->config->hasBinds()) return $instance;
    
    foreach ($this->config->getBinds() as $name => $binds) {
      foreach ($binds as $bind) {
        if ($bind->hasSetter()) {
          $injectionMethod = $bind->getSetter();
        } else {
          $injectionMethod = "set" . ucfirst($name);
        }
        
        $implClassName = $bind->getImplementation();
        
        if (in_array($injectionMethod, get_class_methods($instance))) {
          $argumentInstance = $this->newInstanceWithConstruct($reflection, $implClassName);
          $instance->$injectionMethod($argumentInstance);
        }
      }
    }
    
    return $instance;
  }
  
  protected function newInstanceWithConstruct($reflection, $className)
  {
    if ($this->config->hasConstruct($reflection->getName())) {
      $construct = $this->config->getConstruct($className);
      $constructArguments = array();
      
      foreach ($construct->getConstructs() as $constructValue) {
        if ($this->exists($constructValue)) {
          $instance = $this->constructInstance($constructValue);
          $constructArguments[] = $this->applyAspect($instance);
        } else {
          $constructArguments[] = $constructValue;
        }
      }
      
      $instance = $reflection->newInstanceArgs($constructArguments);
    } else {
      $instance = $this->newInstanceWithConstructDependency($className);
    }
    
    return $instance;
  }
  
  protected function newInstanceWithConstructInAbstract($className, $implClass)
  {
    if ($this->config->hasConstruct($className)) {
      $construct = $this->config->getConstruct($className);
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
    
    if (!$this->config->hasAspect($className)) return $instance;
    
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
      if ($this->config->hasBind($className)) {
        $bind = $this->config->getBind($className);
        
        if (is_array($bind)) {
          $implement = $bind[0]->getImplementation();  
        } else {
          $implement = $bind->getImplementation();  
        }
        
        return $this->newInstance($implement);
      } else {
        throw new Sabel_Exception_Runtime("any '{$className}' implementation not found");
      }
    } else {
      return $this->newInstance($className);
    }
  }
  
  protected function exists($className)
  {
    return (Sabel::using($className) || interface_exists($className));
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
    
    if (!$this->exists($className)) {
      throw new Sabel_Container_Exception_UndefinedClass("{$className} does't exist");
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
      
      if ($reflection === null) continue;
      
      $className = $reflection->getName();
      
      if ($this->config->hasConstruct($className)) {
        $instance = $this->newInstance($className);
      } else {
        if ($reflection->isInstanciatable()) {
          $instance = $this->getInstance($className, $instance);
        } else {
          $instance = $this->newInstance($className);
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
    if (!$this->exists($className)) {
      throw new Sabel_Container_Exception_UndefinedClass("class {$clasName} does't exist");
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
      if (!$this->exists($className)) {
        throw new Sabel_Container_Exception_UndefinedClass("Class {$className} deos not exist");
      }
      
      $reflection = new Sabel_Reflection_Class($className);
      $this->reflectionCache[$className] = $reflection;
      
      return $reflection;
    }
    
    return $this->reflectionCache[$className];
  }
}

/**
 * Sabel Container
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_Container_Bind
{
  private
    $interface,
    $setter,
    $implementation = "";
    
  public function __construct($interface)
  {
    $this->interface = $interface;
  }
  
  public function to($implementation)
  {
    $this->implementation = $implementation;
    
    return $this;
  }
  
  public function setter($methodName)
  {
    $this->setter = trim($methodName);
    
    return $this;
  }
  
  public function hasSetter()
  {
    return (!empty($this->setter));
  }
  
  public function getSetter()
  {
    return $this->setter;
  }
  
  public function getImplementation()
  {
    return $this->implementation;
  }
}

/**
 * Sabel Container
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Container_Construct
{
  private $constructs = array();
  private $source = "";
  
  public function __construct($className)
  {
    $this->source = $className;
  }
  
  public function with($className)
  {
    $this->constructs[] = $className;
    
    return $this;
  }
  
  public function hasConstruct()
  {
    return (count($this->constructs) >= 1);
  }
  
  public function getConstructs()
  {
    return $this->constructs;
  }
}

/**
 * Sabel Container
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_Container_Aspect
{
  private $targetClassName = "";
  
  private $aspect = null;
  private $pointcut = null;
  
  public function __construct($targetClassName)
  {
    $this->targetClassName = $targetClassName;
    $this->aspect = Sabel_Aspect_Aspects::singleton();
  }
  
  public function apply($aspect)
  {
    $this->pointcut = Sabel_Aspect_Pointcut::create($aspect);
    $this->aspect->addPointcut($this->pointcut);
    
    return $this;
  }
  
  public function to($method)
  {
    $this->pointcut->addMethod($method);
    return $this;
  }
  
  public function toMethodRegex($pattern)
  {
    $this->pointcut->setMethodRegex($pattern);
    return $this;
  }
  
  public function toEveryMethods()
  {
    $this->pointcut->toAll();
    return $this;
  }
}
