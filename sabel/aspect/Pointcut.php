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
  
  protected $class      = '';
  protected $method     = '';
  protected $package    = '';
  protected $hasClass   = false;
  protected $hasMethod  = false;
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
  
  public function setClassRegex($rule)
  {
    $this->classRegex = $rule;
    $this->hasClassRegex = true;
    return $this;
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
  
  public function hasMethod()
  {
    return $this->hasMethod;
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