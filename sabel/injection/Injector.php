<?php

/**
 * Sabel_Injection_Injector
 *
 * @category   Injection
 * @package    org.sabel.injection
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Injection_Injector implements Iterator
{
  private $container  = null;
  private $target     = null;
  private $reflection = null;
  private $observers  = array();
  
  public function __construct($target)
  {
    if (!is_object($target))
      throw new Sabel_Exception_Runtime("target is not object.");
      
    $this->container  = Container::create();
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
    $method = $this->reflection->getMethod($method);
    $execute = Sabel_Injection_Calls::doBefore($method, $arg, $this->reflection, $this->target);
    
    $this->notice($method);
    
    if ($execute) {
      $result = $method->invokeArgs($this->target, $arg);
      Sabel_Injection_Calls::doAfter($method, $result, $this->reflection);
      return $result;
    } else {
      return null;
    }
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