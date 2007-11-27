<?php

/**
 * Sabel Container Instance Builder
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Container_DI
{
  private $dependStack = array();
  
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
      
      if (!$reflection->isInterface() && !$reflection->isAbstract()) {
        $instance = $this->getInstance($reflection->getName(), $instance);
      } else {
        $instance = $this->getInstanceWithImplement($reflection->getName(), $instance);
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
