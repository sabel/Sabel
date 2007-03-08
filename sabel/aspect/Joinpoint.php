<?php

class Sabel_Aspect_Joinpoint
{
  protected $target    = null;
  protected $source    = null;
  protected $arguments = array();
  protected $method    = '';
  protected $result    = null;
  protected $exception = null;
  
  public function __construct($target, $source, $arg, $method)
  {
    $this->target    = $target;
    $this->source    = $source;
    $this->arguments = $arg;
    $this->method    = $method;
  }
  
  public function getTarget()
  {
    return $this->target;
  }
  
  public function getSource()
  {
    return $this->source;
  }
  
  public function getReflection()
  {
    return new ReflectionClass($this->target);
  }
  
  public function getMethodReflection()
  {
    return $this->getReflection()->getMethod($this->getMethod());
  }
  
  public function getClassName()
  {
    return $this->getReflection()->getName();
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