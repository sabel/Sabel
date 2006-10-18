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
class Sabel_Aspect_DynamicProxy implements Iterator
{
  private $container  = null;
  private $target     = null;
  private $reflection = null;
  private $observers  = array();
  
  private $parents = array();
  
  public function __construct($target)
  {
    if (!is_object($target))
      throw new Sabel_Exception_Runtime("target is not object.");
      
    $this->container  = Container::create();
    $this->target     = $target;
    $this->reflection = new ReflectionClass($target);
    
    $this->target->this = $this;
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
    if ($method === 'assign') {
      Re::set($this->reflection->getName(), $this->target);
      return;
    }
    
    if (($parent = $this->isParentMethod($method))) {
      return $parent->$method($arg);
    }
    
    $method = $this->reflection->getMethod($method);
    $execute = Sabel_Aspect_Calls::doBefore($method, $arg, $this->reflection, $this->target);
    
    $this->notice($method);
    
    if ($execute) {
      $result = $method->invokeArgs($this->target, $arg);
      $afterResult = Sabel_Aspect_Calls::doAfter($method, $result, $this->reflection);
      if (!is_null($afterResult) && count($afterResult) === 1) return $afterResult[0];
      
      if (count($afterResult) === 0) {
        return $result;
      } else {
        return $afterResult;
      }
    } else {
      return null;
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
  
  protected function notice($method)
  {
    $observers = $this->observers;
    foreach ($observers as $observer) $observer->notice($this, $method);
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function current() {
    return $this->target->current();
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function key()
  {
    return $this->target->key();
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function next()
  {
    return $this->target->next();
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function rewind()
  {
    $this->target->rewind();
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function valid()
  {
    return $this->target->valid();
  }
}