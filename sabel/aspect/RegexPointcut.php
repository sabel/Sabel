<?php

class Sabel_Aspect_RegexPointcut implements Sabel_Aspect_Pointcut
{
  private $classMatcher  = null;
  private $methodMatcher = null;
  
  public function __construct()
  {
    $this->classMatcher  = new Sabel_Aspect_RegexClassMatcher();
    $this->methodMatcher = new Sabel_Aspect_RegexMethodMatcher();
  }
  
  public function setClassPattern($pattern)
  {
    $this->classMatcher->setPattern($pattern);
  }
  
  public function setMethodPattern($pattern)
  {
    $this->methodMatcher->setPattern($pattern);
  }
  
  /**
   * implements Sabel_Aspect_Pointcut interface
   */
  public function getClassMatcher()
  {
    return $this->classMatcher;
  }
  
  /**
   * implements Sabel_Aspect_Pointcut interface
   */
  public function getMethodMatcher()
  {
    return $this->methodMatcher;
  }
}
