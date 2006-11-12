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
  
  const TYPE_BEFORE   = 0;
  const TYPE_AFTER    = 5;
  const TYPE_THROWING = 10;
  
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
  
  public function getReflection()
  {
    return new ReflectionClass($this->target);
  }
  
  public function hasMethod($method)
  {
    return $this->getReflection()->hasMethod($method);
  }
  
  public function assignToView()
  {
    Sabel_Template_Engine::setAttribute(strtolower($this->getReflection()->getName()), $this->target);
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
    $reflection = new ReflectionClass($this->target);
    
    $bcbResult = $this->beforeCallBefore($method, $arg);
    if (!is_null($bcbResult)) return $bcbResult;
    
    $joinpoint = new Sabel_Aspect_Joinpoint($target, $arg, $method);
    
    $aspects = Sabel_Aspect_Aspects::singleton();
    $matches = $aspects->findMatch(array('method' => $method,
                                         'class'  => $reflection->getName()));
    
    $hasMethod = false;
    try {
      $refMethod = $reflection->getMethod($method);
      $hasMethod = true;
    } catch (Exception $e) {
      $hasMethod = false;
    }
    
    $proceed = true;
    try {
      $result = null;
      $proceed = $this->callAspect($joinpoint, $matches, 'around');
      
      if ($proceed) {
        $this->callAspect($joinpoint, $matches, 'before');
        if ($hasMethod) {
          $result = $refMethod->invokeArgs($target, $arg);
        } else if ($this->hasMethodOverload()) {
          eval('$result = $target->$method('.$this->makeArgumentsString($arg).');');
        }
        
        $joinpoint->setResult($result);
        $this->callAspect($joinpoint, $matches, 'after');
        
        return $result;
      }
    } catch (Exception $e) {
      $joinpoint->setException($e);
      $eref = new ReflectionClass($e);
      $matches = $aspects->findExceptionMatch(array('class' => $eref->getName()));
      
      if ($matches->hasMatch()) {
        $this->callAspect($joinpoint, $matches, 'throwing');
      } else {
        throw $e;
      }
    }
  }
  
  protected function beforeCallBefore($method, $arg)
  {
  }
  
  protected function makeArgumentsString($arg)
  {
    if (count($arg) === 0) return '';
    
    $argStrBuf = array();
    for ($i = 0; $i < count($arg); $i++) {
      $argStrBuf[] = '$arg[' . $i . ']';
    }
    return join(', ', $argStrBuf);
  }
  
  protected function callAspect($joinpoint, $matches, $type)
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
  
  /**
   * check target method has a overload method such as __call
   * 
   * @param void
   * @return boolean
   */
  protected function hasMethodOverload()
  {
    $has = true;
    $reflection = new ReflectionClass($this->target);
    
    try {
      $reflection->getMethod('__call');
    } catch (ReflectionException $e) {
      $has = false;
    }
    
    return $has;
  }
}
