<?php

/**
 * Sabel_Aspect_Proxy
 *
 * @category   Aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Proxy
{
  protected $target = null;
  protected $source = null;
  
  public function __construct($taget, $source = null)
  {
    $this->target = $taget;
    $this->source = $source;
  }
  
  public function setSourceClass($source)
  {
    $this->source = $source;
  }
  
  public function getSourceClass()
  {
    return $this->source;
  }
  
  public function setTargetClass($target)
  {
    $this->target = $target;
  }
  
  public function getTargetClass()
  {
    return $this->target;
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
    $target = $this->target;
    $source = $this->source;
    
    $joinpoint = new Sabel_Aspect_Joinpoint($target, $arg, $method);
    
    $aspects = Sabel_Aspect_Aspects::singleton();
    $matches = $aspects->findMatch(array('method' => $method));
    
    $reflection = new ReflectionClass($this->target);
    $method = $reflection->getMethod($method);
    
    try {
      $proceed = $this->callAspect($joinpoint, $matches, 'around');
      if ($proceed) {
        $this->callAspect($joinpoint, $matches, 'before');
        $result = $method->invokeArgs($target, $arg);
        
        $joinpoint->setResult($result);
        $this->callAspect($joinpoint, $matches, 'after');
        
        return $result;
      }
    } catch (Exception $e) {
      $joinpoint->setException($e);
      return $this->callAspect($joinpoint, $matches, 'throwing');
    }
  }
  
  private function callAspect($joinpoint, $matches, $type)
  {
    $called = false;
    $result = false;
    
    foreach ($matches as $aspect) {
      $aspectReflect = new ReflectionClass($aspect);
      if ($aspectReflect->hasMethod($type)) {
        $called = true;
        $result = $aspect->$type($joinpoint);
      }
      unset($aspectReflect);
    }
    
    return ($called) ? $result : true;
  }
}