<?php

/**
 * Sabel_Aspect_DynamicProxy
 *
 * @category   Aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_DynamicProxy
{
  private static $container = null;
  
  private $target     = null;
  private $reflection = null;
  
  private $parents   = array();
  private $observers = array();
  
  private $bothAspects = array();
  private $bothAspectsResults = array();
  private $beforeAspects = array();
  private $beforeAspectsResults = array();
  private $afterAspects = array();
  private $afterAspectsResults = array();
  
  public function __construct($target)
  {
    if (!is_object($target))
      throw new Sabel_Exception_Runtime("target is not object.");
      
    if(is_null(self::$container)) self::$container = Container::create();
    $this->target     = $target;
    $this->reflection = new ReflectionClass($target);
  }
  
  public function __set($key, $value)
  {
    $this->target->$key = $value;
  }
  
  public function __get($key)
  {
    return $this->target->$key;
  }
  
  public function __call($method, $arg)
  {
    $target     = $this->target;
    $reflection = $this->reflection;
    
    if (($parent = $this->isParentMethod($method))) {
      return $parent->$method($arg);
    }
    
    try {
      $method = $reflection->getMethod($method);
    } catch (ReflectionException $e) {
      if ($this->hasMethodOverload()) {
        // @todo write testcase of nested __call()
        $argStrBuf = array();
        for ($i = 0; $i < count($arg); $i++) {
          $argStrBuf[] = '$arg[' . $i . ']';
        }
        
        $args = join(', ', $argStrBuf);
        $this->callBeforeAspects($method);
        eval('$ret = $this->target->$method(' . $args . ');');
        $this->callAfterAspects($method, $ret);
        $this->callBothAspects($method, $ret);
        return $ret;
      }
    }
    
    $this->callBeforeAspects($method);
    $execute = Sabel_Aspect_Calls::doBefore($method, $arg, $reflection, $target);
    $this->notice($method);
    
    if ($execute) {
      $result = $method->invokeArgs($target, $arg);
      $this->callAfterAspects($method, $result);
      $afterResult = Sabel_Aspect_Calls::doAfter($method, $result, $reflection);
      if (!is_null($afterResult) && count($afterResult) === 1) return $afterResult[0];
      
      if (count($afterResult) === 0) {
        return $result;
      } else {
        return $afterResult;
      }
    }
    
    return null;
  }
  
  public function assignToView()
  {
    Re::set(strtolower($this->reflection->getName()), $this->target);
    return;
  }
  
  public function bothResults()
  {
    return $this->beforeAspectsResults;
  }
  
  public function bothResult($name)
  {
    if (isset($this->bothAspectsResults[$name])) {
      return $this->bothAspectsResults[$name];
    } else {
      return null;
    }
  }
  
  public function bothAspect($name, $function)
  {
    $this->bothAspects[$name] = $function;
  }
  
  public function beforeResults()
  {
    return $this->beforeAspectsResults;
  }
  
  public function beforeResult($name)
  {
    if (isset($this->beforeAspectsResults[$name])) {
      return $this->beforeAspectsResults[$name];
    } else {
      return null;
    }
  }
  
  public function beforeAspect($name, $function)
  {
    // if (function_exists($function)) $this->beforeAspects[$name] = $function;
    $this->beforeAspects[$name] = $function;
  }
  
  public function afterResults()
  {
    return $this->afterResults;
  }
  
  public function afterResult($functionName)
  {
    if (isset($this->afterAspectsResults[$functionName])) {
      return $this->afterAspectsResults[$functionName];
    } else {
      return null;
    }
  }
  
  public function afterAspect($name, $function)
  {
    // if (function_exists($function)) $this->afterAspects[] = $function;
    $this->afterAspects[$name] = $function;
  }
  
  protected function callBothAspects($method)
  {
    $aspects = $this->bothAspects;
    foreach ($aspects as $name => $function) {
      $this->bothAspectsResults[$name] = $function($this->target, $method);
    }
  }
  
  protected function callBeforeAspects($method)
  {
    $aspects = $this->beforeAspects;
    foreach ($aspects as $name => $function) {
      $this->beforeAspectsResults[$name] = $function($this->target, $method);
    }
  }
  
  protected function callAfterAspects($method, $result)
  {
    $aspects = $this->afterAspects;
    foreach ($aspects as $name => $function) {
      $this->afterAspectsResults[$name] = $function($this->target, $method, $result);
    }
  }
  
  protected function hasParent()
  {
    return (count($this->parents) > 0);
  }
  
  protected function isParentMethod($method)
  {
    $result = false;
    $parents = $this->parents;
    foreach ($parents as $parent) {
      if ($parent->hasMethod($method)) {
        $result = true;
        break;
      }
    }
    
    if ($result) {
      return $parent;
    } else {
      return $result;
    }
  }
  
  public function hasMethod($method)
  {
    return $this->reflection->hasMethod($method);
  }
  
  public function getTarget()
  {
    return $this->target;
  }
  
  public function getReflection()
  {
    return $this->reflection;
  }
  
  public function observe($observer)
  {
    $this->observers[] = $observer;
  }
  
  public function inherit($parent)
  {
    $parentInstance = Container::create()->load($parent);
    $this->parents[] = $parentInstance;
    return $this;
  }
  
  protected function hasMethodOverload()
  {
    $has = true;
    try {
      $this->reflection->getMethod('__call');
    } catch (ReflectionException $e) {
      $has = false;
    }
    return $has;
  }
  
  protected function notice($method)
  {
    $observers = $this->observers;
    foreach ($observers as $observer) $observer->notice($this, $method);
  }
}