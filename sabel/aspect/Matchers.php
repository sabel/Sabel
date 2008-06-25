<?php

class Sabel_Aspect_RegexMethodMatcher implements Sabel_Aspect_MethodMatcher
{
  private $pattern = "";
  
  public function setPattern($pattern)
  {
    $this->pattern = $pattern;
  }
  
  public function matches($method, $class)
  {
    return (boolean) preg_match($this->pattern, $method);
  }
}

class Sabel_Aspect_RegexClassMatcher implements Sabel_Aspect_ClassMatcher
{
  private $pattern = "";
  
  public function setPattern($pattern)
  {
    $this->pattern = $pattern;
  }
  
  public function matches($class)
  {
    return (boolean) preg_match($this->pattern, $class);
  }
}

abstract class Sabel_Aspect_StaticMethodMatcher implements Sabel_Aspect_MethodMatcher
{
  public function matches($method, $class){}
}

abstract class Sabel_Aspect_StaticMethodMatcherPointcut
             extends Sabel_Aspect_StaticMethodMatcher
               implements Sabel_Aspect_Pointcut
{
  private $classMatcher = null;
  
  public function setClassMatcher(Sabel_Aspect_ClassMatcher $matcher)
  {
    $this->classMatcher = $matcher;
  }
  
  /**
   * implements from Pointcut interface
   */
  public function getClassMatcher()
  {
    return $this->classMatcher;
  }
  
  /**
   * implements from Pointcut interface
   */
  public function getMethodMatcher()
  {
    return $this;
  }
}

abstract class Sabel_Aspect_StaticClassNameMatcher implements Sabel_Aspect_ClassMatcher
{
  public function matches($class) {}
}