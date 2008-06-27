<?php

class Sabel_Tests_Aspect_SimpleBeforeAdvice implements Sabel_Aspect_MethodBeforeAdvice
{
  private $calledMethods = array();
  
  public function before($method, $arguments, $target)
  {
    $this->calledMethods[] = $method->getName();
  }
  
  public function getCalledMethods()
  {
    return $this->calledMethods;
  }
}

class Sabel_Tests_Aspect_SimpleAfterReturningAdvice implements Sabel_Aspect_MethodAfterReturningAdvice
{
  private $results = array();
  
  public function after($method, $arguments, $target, $returnValue)
  {
    $this->results[] = $returnValue;
  }
  
  public function getResults()
  {
    return $this->results;
  }
}

class Sabel_Tests_Aspect_SimpleThrowsAdvice implements Sabel_Aspect_MethodThrowsAdvice
{
  private $throwsMessage = "";
  
  public function throws($method, $arguments, $target, $exception)
  {
    $this->throwsMessage = $exception->getMessage();
  }
  
  public function getThrowsMessage()
  {
    return $this->throwsMessage;
  }
}

class DefaultPointcuts extends Sabel_Aspect_Pointcuts
{
}

class Sabel_Tests_Aspect_TargetClass
{
  public function getX()
  {
    return "X";
  }
  
  public function setX($arg)
  {
    return $arg;
  }
  
  public function setY()
  {
    
  }
  
  public function getY()
  {
    return "Y";
  }
  
  public function getName()
  {
  }
  
  public function willThrowException()
  {
    throw new Sabel_Exception_Runtime("throws");
  }
}

class Sabel_Tests_Aspect_TargetClass2 extends Sabel_Tests_Aspect_TargetClass
{
}

class StaticPointcut implements Sabel_Aspect_Pointcut
{
  public function getClassMatcher()
  {
    return new MyStaticClassNameMatcher();
  }
  
  public function getMethodMatcher()
  {
    return new MyMethodMatcher();
  }
}

class MyStaticClassNameMatcher extends Sabel_Aspect_StaticClassNameMatcher
{
  public function matches($class)
  {
    return ($class->getName() === "Sabel_Tests_Aspect_TargetClass");
  }
}

class MyMethodMatcher extends Sabel_Aspect_StaticMethodMatcher
{
  public function matches($method, $class)
  {
    return ($method === "setX");
  }
}

class MyStaticMethodMatcherPointcutAdvisor extends Sabel_Aspect_StaticMethodMatcherPointcutAdvisor
{
  public function __construct()
  {
    defineClass("MyClassMatcher", '
      class %s implements Sabel_Aspect_ClassMatcher
      {
        public function matches($class)
        {
          return true;
        }
      }
    ');
    
    $this->setClassMatcher(new MyClassMatcher());
  }
  
  public function matches($method, $class)
  {
    return preg_match("/get+/", $method);
  }
}

class MyRegexMethodMatcherPointcutAdvisor extends Sabel_Aspect_StaticMethodMatcherPointcutAdvisor
{
  private $pattern;
  
  public function __construct()
  {
    defineClass("MyClassMatcher", '
      class %s implements Sabel_Aspect_ClassMatcher
      {
        public function matches($class)
        {
          return true;
        }
      }
    ');
    
    $this->setClassMatcher(new MyClassMatcher());
  }
  
  public function setPattern($pattern)
  {
    $this->pattern = $pattern;
  }
  
  public function matches($method, $class)
  {
    return preg_match($this->pattern, $method);
  }
}

function defineClass($className, $class)
{
  if (!class_exists($className)) {
    eval(sprintf($class, $className));
  }
}
