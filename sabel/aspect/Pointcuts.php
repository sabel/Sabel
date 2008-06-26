<?php

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

class Sabel_Aspect_DefaultRegexPointcut implements Sabel_Aspect_RegexPointcut
{
  private $classMatcher = null;
  private $methodMatcher = null;
  
  public function __construct()
  {
    $this->classMatcher  = new Sabel_Aspect_RegexClassMatcher();
    $this->methodMatcher = new Sabel_Aspect_RegexMethodMatcher();
  }
  
  public function setClassMatchPattern($pattern)
  {
    $this->classMatcher->setPattern($pattern);
  }
  
  public function setMethodMatchPattern($pattern)
  {
    $this->methodMatcher->setPattern($pattern);
  }
  
  public function getClassMatcher()
  {
    return $this->classMatcher;
  }
  
  public function getMethodMatcher()
  {
    return $this->methodMatcher;
  }
}

class Sabel_Aspect_DefaultPointcuts extends Sabel_Aspect_Pointcuts
{
  
}