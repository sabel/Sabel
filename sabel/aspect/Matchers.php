<?php

abstract class Sabel_Aspect_StaticMethodMatcher implements Sabel_Aspect_MethodMatcher
{
  public function matches($method, $class){}
}

abstract class Sabel_Aspect_StaticClassNameMatcher implements Sabel_Aspect_ClassMatcher
{
  public function matches($class) {}
}

class Sabel_Aspect_RegexClassMatcher implements Sabel_Aspect_ClassMatcher,
                                                Sabel_Aspect_RegexMatcher
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

class Sabel_Aspect_RegexMethodMatcher implements Sabel_Aspect_MethodMatcher,
                                                 Sabel_Aspect_RegexMatcher
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