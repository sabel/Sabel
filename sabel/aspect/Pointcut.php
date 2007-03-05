<?php

/**
 * Sabel_Aspect_Pointcut
 *
 * @category   Aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Pointcut
{
  const AROUND =  0;
  const BEFORE =  5;
  const AFTER  = 10;
  
  protected $type = self::BEFORE;
  
  protected $name = '';
  protected $aspect = null;
  
  protected $toAll = false;
  
  protected $anyException = false;
  protected $exception = false;
  
  protected $exceptionClass = '';
  protected $hasExceptionClass = false;
  protected $exceptionClassRegex = '';
  protected $hasExceptionClassRegex = false;
  
  protected $class      = '';
  protected $method     = '';
  protected $methods    = array();
  protected $package    = '';
  protected $hasClass   = false;
  protected $hasMethod  = false;
  protected $hasMethods = false;
  protected $hasPackage = false;
  
  protected $classRegex      = '';
  protected $methodRegex     = '';
  protected $packageRegex    = '';
  protected $hasClassRegex   = false;
  protected $hasMethodRegex  = false;
  protected $hasPackageRegex = false;
  
  public function __construct($name)
  {
    $this->name   = $name;
    $this->aspect = new $name();
  }
  
  public static function create($name)
  {
    return new self($name);
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getAspect()
  {
    return $this->aspect;
  }
  
  public function hasException()
  {
    return $this->exception;
  }
  
  public function anyException()
  {
    $this->exception = true;
    $this->anyException = true;
    return $this;
  }
  
  public function hasAnyException()
  {
    return $this->anyException;
  }
  
  public function setExceptionClass($class)
  {
    $this->exception = true;
    $this->hasExceptionClass = true;
    $this->exceptionClass = $class;
    return $this;
  }
  
  public function hasExceptionClass()
  {
    return $this->hasExceptionClass;
  }
  
  public function getExceptionClass()
  {
    return $this->exceptionClass;
  }
  
  public function setExceptionClassRegex($class)
  {
    $this->exception = true;
    $this->hasExceptionClassRegex = true;
    $this->exceptionClassRegex = $class;
    return $this;
  }
  
  public function getExceptionClassRegex()
  {
    return $this->exceptionClassRegex;
  }
  
  public function hasExceptionClassRegex()
  {
    return $this->hasExceptionClassRegex;
  }
  
  public function setClass($class)
  {
    $this->class = $class;
    $this->hasClass = true;
    return $this;
  }
  
  public function hasClass()
  {
    return $this->hasClass;
  }
  
  public function getClass()
  {
    return $this->class;
  }
  
  public function setClassRegex($rule)
  {
    $this->classRegex = $rule;
    $this->hasClassRegex = true;
    return $this;
  }
  
  public function getClassRegex()
  {
    return $this->classRegex;
  }
  
  public function hasClassRegex()
  {
    return $this->hasClassRegex;
  }
  
  public function setMethod($method)
  {
    $this->method = $method;
    $this->hasMethod = true;
    return $this;
  }
  
  public function addMethod($method)
  {
    $this->methods[] = $method;
    $this->hasMethods = true;
    return $this;
  }
  
  public function setMethods($methods)
  {
    $this->methods = $methods;
    $this->hasMethods = true;
    return $this;
  }
  
  public function getMethod()
  {
    return $this->method;
  }
  
  public function getMethods()
  {
    return $this->methods;
  }
  
  public function hasMethod()
  {
    return $this->hasMethod;
  }
  
  public function hasMethods()
  {
    return $this->hasMethods;
  }
  
  public function setMethodRegex($rule)
  {
    $this->methodRegex = $rule;
    $this->hasMethodRegex = true;
    return $this;
  }
  
  public function getMethodRegex()
  {
    return $this->methodRegex;
  }
  
  public function hasMethodRegex()
  {
    return $this->hasMethodRegex;
  }
  
  public function toAll()
  {
    $this->toAll = true;
    return $this;
  }
  
  public function hasToAll()
  {
    return $this->toAll;
  }
  
  public function asAround()
  {
    $this->type = self::AROUND;
    return $this;
  }
  
  public function asBefore()
  {
    $this->type = self::BEFORE;
    return $this;
  }
  
  public function asAfter()
  {
    $this->type = self::AFTER;
    return $this;
  }
}