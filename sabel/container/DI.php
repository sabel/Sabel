<?php

/**
 * Sabel Container Instance Builder
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Container_DI
{
  private $injection = null;
  private $injector = null;
  
  private $dependStack = array();
  
  public function __construct(Sabel_Container_Injection $injection, Sabel_Container_Injector $injector)
  {
    $this->injection = $injection;
    $this->injector = $injector;
  }
  
  /**
   * load instance of $className;
   *
   * @return object constructed instance
   */
  public function load($className)
  {
    $this->parseDependency($className);
    return $this->constructInstance();
  }
  
  /**
   * resolve dependency chain
   * 
   * @throws Sabel_Exception_Runtime when class does not exists
   */
  private final function parseDependency($class)
  {
    $method = "__construct";
    
    if (!class_exists($class)) {
      throw new Sabel_Exception_Runtime($class . " does not exists");
    }
    
    $reflectionClass = new Sabel_Reflection_Class($class);
    
    // push to Stack class name
    $this->dependStack[] = $reflectionClass;
    
    if ($reflectionClass->hasMethod($method)) {
      // parameters loop
      $refMethod = $reflectionClass->getMethod($method);
      foreach ($refMethod->getParameters() as $param) {
        // check parameter required class
        if ($dependClass = $param->getClass()) {
          // recursive call if class also depend another class.
          $depend = $dependClass->getName();
          if ($this->isParameterDependOnClass($depend)) {
            $this->parseDependency($depend);
          } else {
            $this->dependStack[] = new Sabel_Reflection_Class($depend);
          }
        }
      }
    }
    
    return $this;
  }
  
  /**
   * constructing depend instance
   *
   * @return object
   */
  private final function constructInstance()
  {
    $stackCount =(int) count($this->dependStack);
    
    if ($stackCount < 1) {
      $msg = "invalid stack count:" . var_export($this->dependStack, 1);
      throw new Sabel_Exception_Runtime($msg);
    }
    
    $instance = null;
    
    for ($i = 0; $i < $stackCount; ++$i) {
      $reflection = array_pop($this->dependStack);
      
      $className = $reflection->getName();
      
      if ($this->injection->hasConstruct($className)) {
        $instance = $this->injector->newInstance($className);
      } else {
        if (!$reflection->isInterface() && !$reflection->isAbstract()) {
          $instance = $this->getInstance($className, $instance);
        } else {
          $instance = $this->getInstanceWithImplement($className, $instance);
        }
      }
      
      unset($reflection);
    }
    
    return $instance;
  }
  
  private final function getInstance($className, $instance = null)
  {
    if (!class_exists($className)) {
      throw new Sabel_Exception_Runtime("class doesn't exists");
    }
    
    if ($instance) {
      return new $className($instance);
    } else {
      return new $className();
    }
  }
  
  private final function getInstanceWithImplement($className, $instance = null)
  {
    if (!class_exists($className)) {
      throw new Sabel_Exception_Runtime("class doesn't exists");
    }
    
    if ($instance) {
      return new $className($instance);
    } else {
      return new $className();
    }
  }
  
  public function isParameterDependOnClass($class, $method = "__construct")
  {
    $refClass  = new ReflectionClass($class);
    $result = null;
    
    if ($refClass->isInterface()) {
      $result = false;
    } elseif ($refClass->hasMethod($method)) {
      $refMethod = new ReflectionMethod($class, $method);
      $result = (count($refMethod->getParameters() !== 0));
    }
    
    return $result;
  }
}
