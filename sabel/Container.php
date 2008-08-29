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
  
  protected $instance = array();
  
  /**
   *
   * @param mixed $config object | string
   */
  public static function create($config = null)
  {
    if ($config === null) return new self();
    
    if (is_object($config) && $config instanceof Sabel_Container_Injection) {
      return new self($config);
    } elseif (is_string($config)) {
      if (isset(self::$configs[$config])) {
        $config = self::$configs[$config];
        
        if (!is_object($config)) {
          $config = new $config();
        }
        
        return new self($config);
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
  public static function load($class, $config = null)
  {
    if (is_object($config) && $config instanceof Sabel_Container_Injection) {
      return self::create($config)->newInstance($class);
    } elseif (is_string($config)) {
      if (self::hasConfig($config)) {
        return self::create($config)->newInstance($class);
      } else {
        if (self::hasConfig("default")) {
          return self::load($class, "default");  
        } else {
          self::addConfig("default", new Sabel_Container_DefaultInjection());
          return self::load($class);
        }
      }
    } elseif ($config === null) {
      $config = "default";
      
      if (self::hasConfig($config)) {
        return self::load($class, $config);  
      } else {
        self::addConfig($config, new Sabel_Container_DefaultInjection());
      }
      
      return self::load($class, $config);
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
  
  /**
   * hasConfig
   *
   * @param string $name
   * @return boolean
   */
  public static function hasConfig($name)
  {
    return isset(self::$configs[$name]);
  }
  
  /**
   * getConfig
   *
   * @param string $name
   * @return Sabel_Container_Injection
   * @throws Sabel_Container_Exception_InvalidConfiguration
   */
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
  
  /**
   * clear all injection configs
   */
  public static function clearAllConfigs()
  {
    self::$configs = array();
  }
  
  /**
   * default constructer
   *
   * @param Sabel_Container_Injection $injection
   */
  public function __construct($config = null)
  {
    if ($config !== null) {
      $config->configure();
      $this->config = $config;
    }
    
    register_shutdown_function(array($this, "storeLifecycle"));
  }
  
  /**
   * get new class instance from class name
   *
   * @param string $className
   * @return object
   */
  public function newInstance($class, $arguments = null)
  {
    $reflection = $this->getReflection($class);
    
    if (is_object($class)) {
      $instance = $class;
    } else {
      $className = $class;
      
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
    }
    
    if ($reflection->hasMethod("setContainerContext")) {
      $instance->setContainerContext($this);
    }
    
    $instance = $this->injectToSetter($reflection, $instance);
    $instance = $this->recover($reflection, $instance);
    $instance = $this->applyAspect($instance);
    
    $this->instance[] = $instance;
    
    return $instance;
  }
  
  /**
   * inject to setter
   *
   * @param Sabel_Reflection_Class $reflection
   * @param Object $sourceInstance
   */
  protected function injectToSetter($reflection, $sourceInstance)
  {
    if (self::hasConfig("default")) {
      $defaultConfig = self::getConfig("default");
      $defaultConfig->configure();

      if ($defaultConfig->hasBinds()) {
        $this->processSetter($reflection, $sourceInstance, $defaultConfig);
      }
    }

    if ($this->config->hasBinds()) {
      $this->processSetter($reflection, $sourceInstance, $this->config);
    }

    return $sourceInstance;
  }

  private function processSetter($reflection, $sourceInstance, $config)
  {
    foreach ($config->getBinds() as $name => $binds) {
      foreach ($binds as $bind) {
        if ($bind->hasSetter()) {
          $injectionMethod = $bind->getSetter();
        } else {
          $injectionMethod = "set" . ucfirst($name);
        }
        
        $implClassName = $bind->getImplementation();
        
        if (in_array($injectionMethod, get_class_methods($sourceInstance))) {
          $argumentInstance = $this->newInstanceWithConstruct($reflection, $implClassName);
          $sourceInstance->$injectionMethod($argumentInstance);
        }
      }
    }
  }
  
  protected function recover($reflection, $instance)
  {
    $className = get_class($instance);
    
    if ($this->config->hasLifecycle($className)) {
      $lifecycleConfig = $this->config->getLifecycle($className);
      
      if ($lifecycleConfig->isApplication()) {
        $backend = $lifecycleConfig->getBackend("Application");
        
        if ($backend->isStored($className)) {
          $backend->fetch($className, $instance, $reflection, $reflection->getProperties());
        }
      }
    }
    
    return $instance;
  }
  
  protected function applyAspect($instance)
  {
    if ($instance === null) {
      throw new Sabel_Exception_Runtime("invalid instance " . var_export($instance, 1));
    }
    
    $className = get_class($instance);
    $adviceClasses = array();
    
    $aspects = $this->config->getAspects();
    
    if (count($aspects) === 0) {
      return $instance;
    }
    
    $interfaces = $this->getReflection($instance)->getInterfaces();
    
    if (count($interfaces) >= 1) {
      foreach ($aspects as $aspect) {
        foreach ($interfaces as $implementInterface) {
          $implementName = $implementInterface->name;
          
          $parent = $aspect->getName();
          if ($implementName instanceof $parent || $aspect->getName() === $implementName) {
            $adviceClasses[] = $aspect->getAdvice();
          }
        }
      }
    } else {
      foreach ($aspects as $aspect) {
        $parent = $aspect->getName();
        
        if ($instance instanceof $parent) {
          $className = $aspect->getName();
          break;
        }
      }
      
      if (!$this->config->hasAspect($className)) return $instance;
      
      $adviceClasses[] = $this->config->getAspect($className)->getAdvice();
    }
    
    $weaverClass = $this->config->getWeaver();
    $factory = new Sabel_Aspect_RegexFactory();
    return $factory->build($weaverClass, $instance, $adviceClasses)->getProxy();
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
    $reflection = $this->getReflection($className);
    
    if (!$reflection->isInterface()) {
      return $this->newInstance($className);
    }
    
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
  protected function getReflection($class)
  {
    if (is_object($class)) {
      $className = get_class($class);
    } else {
      $className = $class;
    }
    
    if (!isset($this->reflectionCache[$className])) {
      if (!$this->exists($className)) {
        throw new Sabel_Container_Exception_UndefinedClass("Class {$className} deos not exist");
      }
      
      $reflection = new Sabel_Reflection_Class($class);
      $this->reflectionCache[$className] = $reflection;
      
      return $reflection;
    }
    
    return $this->reflectionCache[$className];
  }
  
  public function storeLifecycle()
  {
    $config = $this->config;
    
    foreach ($this->instance as $i) {
      $className = get_class($i);
      
      $reflection = $this->getReflection($className);
      
      if ($config->hasLifecycle($className)) {
        $lifecycleConfig = $config->getLifecycle($className);
        
        if ($lifecycleConfig->isApplication()) {
          $backend = $lifecycleConfig->getBackend("Application");
          
          if (!$backend->isStored($className)) {
            $values = $this->getProperties($i, $reflection);
            
            $backend->store($className, $values);
          }
        }
      }
    }
  }
  
  private function getProperties($instance, $reflection)
  {
    $values = array();
    $properties = $reflection->getProperties();
    
    foreach ($properties as $property) {
      $pname = $property->getName();
      $getterMethod = "get" . ucfirst($pname);
      
      if ($reflection->hasMethod($getterMethod)) {
        $value = $instance->$getterMethod();
        $values[$pname] = $value;
      }
    }
    
    return $values;
  }
}

/**
 * Sabel Container Bind
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
 * Sabel Container Construct
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
 * Sabel Container Aspect
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_Container_Aspect
{
  private $className = "";
  private $adviceClass = "";
  
  public function __construct($className)
  {
    $this->className = $className;
  }
  
  public function getName()
  {
    return $this->className;
  }
  
  public function advice($adviceClass)
  {
    $this->adviceClass = $adviceClass;
    return $this;
  }
  
  public function getAdvice()
  {
    return $this->adviceClass;
  }
}
