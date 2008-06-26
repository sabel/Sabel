<?php

class Sabel_Aspect_DefaultMethodInvocation implements Sabel_Aspect_MethodInvocation
{
  private $proxy = null;
  
  private $class  = null;
  private $reflection = null;
  
  private $method   = null;
  private $argument = null;
  
  private $advices = array();
  
  private $currentAdviceIndex = -1;
  private $lastAdviceIndex    = -1;
  
  public function __construct($proxy, $class, $method = null, $argument = null)
  {
    $this->proxy = $proxy;
    $this->class = $class;
    $this->reflection = new Sabel_Reflection_Class($class);
    
    if ($method   !== null) $this->method   = $method;
    if ($argument !== null) $this->argument = $argument;
  }
  
  public function setMethod($method)
  {
    $this->method = $method;
  }
  
  public function setArgument($argument)
  {
    $this->argument = $argument;
  }
  
  public function reset($method, $argument)
  {
    $this->method   = $method;
    $this->argument = $argument;
    
    $this->currentAdviceIndex = -1;
  }
  
  public function setAdvices($advices)
  {
    $this->advices = $advices;
    $this->lastAdviceIndex = count($advices);
  }
  
  public function getArguments()
  {
    return $this->argument;
  }
  
  public function getMethod()
  {
    return $this->reflection->getMethod($this->method);
  }
  
  public function getStaticPart()
  {
  }
  
  public function getThis()
  {
    return $this->reflection;
  }
  
  public function proceed()
  {
    if ($this->lastAdviceIndex === -1) {
      return $this->reflection
                  ->getMethod($this->method)
                  ->invokeArgs($this->class, $this->argument);
    } elseif ($this->currentAdviceIndex === $this->lastAdviceIndex - 1) {
      return $this->reflection
                  ->getMethod($this->method)
                  ->invokeArgs($this->class, $this->argument);
    }
    
    if (isset($this->advices[++$this->currentAdviceIndex])) {
      $advice = $this->advices[$this->currentAdviceIndex];
      
      if ($advice instanceof Sabel_Aspect_MethodInterceptor) {
        return $advice->invoke($this);  
      } elseif ($advice instanceof Sabel_Aspect_MethodBeforeAdvice) {
        $advice->before($this->method, $this->argument, $this->class);
        $this->proceed();
      }
    }
  }
}