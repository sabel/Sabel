<?php

/**
 * Sabel_Aspect_Proxy
 *
 * @category   Aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Proxy
{
  private static $aspects = null;
  
  private $target = null;
  private $targetClassName = null;
  
  private $source = null;
  
  private $matchesCache   = array();
  private $joinpointCache = array();
  private $targetReflectionCache = null;
  
  const TYPE_AROUND    = "around";
  const TYPE_BEFORE    = "before";
  const TYPE_AFTER     = "after";
  const TYPE_EXCEPTION = "exception";
  
  public function __construct($target)
  {
    $this->target = $target;
    $this->targetClassName = get_class($target);
    
    $trace = debug_backtrace();
    
    for ($i = 1; $i < 5; $i++) {
      if (isset($trace[$i]["object"])) {
        $this->source = $trace[$i]["object"];
        break;
      }
    }
    
    if (self::$aspects === null) {
      self::$aspects = Sabel_Aspect_Aspects::create();
    }
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
    
    $reflection = $this->__getTargetReflection__();
    $joinpoint  = $this->__getJoinpoint__($method, $arg);
    $matches    = $this->__getMatches__($method, $reflection);
    
    $proceed = true;
    try {
      $result  = null;
      $proceed = $this->__callAspect__($joinpoint, $matches, self::TYPE_BEFORE);
      
      if ($proceed !== false) {
        $reflection->getMethod($method)->invokeArgs($target, $arg);
        
        $joinpoint->setResult($result);
        $this->__callAspect__($joinpoint, $matches, self::TYPE_AFTER);
        
        return $result;
      }
    } catch (Exception $e) {
      $joinpoint->setException($e);
      $exceptionReflection = new Sabel_Reflection_Class($e);
      $matches = $this->aspects->findExceptionMatch(array("class" => $exceptionReflection->getName()));
      
      $this->__callAspect__($joinpoint, $matches, self::TYPE_EXCEPTION);
      throw $e;
    }
  }
  
  public function __getSource__()
  {
    return $this->source;
  }

  public function __setSourceClass__($source)
  {
    $this->source = $source;
  }
  
  public function __getSourceClass__()
  {
    return $this->source;
  }
  
  public function __setTarget__($target)
  {
    $this->target = $target;
  }
  
  public function __getTarget__()
  {
    return $this->target;
  }
  
  public function __getReflection__()
  {
    return new Sabel_Reflection_Class($this->target);
  }
  
  public function __hasMethod__($method)
  {
    return $this->getReflection()->hasMethod($method);
  }
  
  protected function __callAspect__($joinpoint, $matches, $position)
  {
    $called = false;
    $result = false;
    
    foreach ($matches as $aspect) {
      $called = true;
      $result = $aspect->$position($joinpoint);
    }
    
    return ($called) ? $result : true;
  }
  
  protected function __getAspects__()
  {
    
  }
  
  protected function __getTargetReflection__()
  {
    if ($this->targetReflectionCache === null) {
      $this->targetReflectionCache = new Sabel_Reflection_Class($this->target);
    }
    
    return $this->targetReflectionCache;
  }
  
  /**
   * get matches
   *
   * @return array
   */
  protected function __getMatches__($method)
  {
    $name = $this->targetClassName;
    $key  = $name . "::" . $method;
    
    if (!isset($this->matchesCache[$key])) {
      $matches = self::$aspects->findMatch($name, array("method" => $method,
                                                        "class"  => $name));
      
      $this->matchesCache[$key] = $matches;
      
      return $matches;
    }
    
    return $this->matchesCache[$key];
  }
  
  protected function __getJoinpoint__($method, $arg)
  {
    if (!isset($this->joinpointCache[$method])) {
      $this->joinpointCache[$method] = new Sabel_Aspect_Joinpoint($this->target);
    }
    
    $joinpoint = $this->joinpointCache[$method];
    $joinpoint->setMethod($method);
    $joinpoint->setArguments($arg);
    
    return $joinpoint;
  }
  
  /**
   * check target method has a overload method such as __call
   * 
   * @param void
   * @return boolean
   */
  protected function __hasMethodOverload__()
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

class Sabel_Aspect_Joinpoint
{
  protected $target    = null;
  protected $arguments = array();
  protected $method    = "";
  protected $result    = null;
  protected $exception = null;
  
  public function __construct($target)
  {
    $this->target    = $target;
  }
  
  public function setArguments($arg)
  {
    $this->arguments = $arg;
  }
  
  public function getArguments()
  {
    return $this->arguments;
  }
  
  public function getArgument($index)
  {
    $arguments = $this->arguments;
    if (isset($arguments[$index])) {
      return $this->arguments[$index];
    }
  }
  
  public function setMethod($method)
  {
    $this->method = $method;
  }
  
  public function getMethod()
  {
    return $this->method;
  }
  
  public function setResult($result)
  {
    $this->result = $result;
  }
  
  public function hasResult()
  {
    return ($this->result === null) ? false : $this->result;
  }
  
  public function getResult()
  {
    return $this->result;
  }
  
  public function setException($e)
  {
    $this->exception = $e;
  }
  
  public function hasException()
  {
    return ($this->exception === null) ? false : true ;
  }
  
  public function getException()
  {
    return $this->exception;
  }
}
