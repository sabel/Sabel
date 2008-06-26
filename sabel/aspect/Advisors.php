<?php

class Sabel_Aspect_StaticMethodMatcherPointcutAdvisor
    extends Sabel_Aspect_StaticMethodMatcherPointcut
      implements Sabel_Aspect_PointcutAdvisor
{
  private $advice = null;
  
  public function setAdvice(Sabel_Aspect_Advice $interceptor)
  {
    $this->advice = $interceptor;
  }
  
  public function getAdvice()
  {
    return $this->advice;
  }
  
  public function isPerInstance()
  {
  }
  
  public function getPointcut()
  {
    return $this;
  }
}

class Sabel_Aspect_RegexMatcherPointcutAdvisor
    implements Sabel_Aspect_PointcutAdvisor
{
  /**
   * @var Sabel_Aspect_Pointcut
   */
  private $pointcut = null;
  
  /**
   * @var Sabel_Aspect_Advice
   */
  private $advice   = null;
  
  public function __construct()
  {
    $this->pointcut = new Sabel_Aspect_DefaultRegexPointcut();
  }
  
  public function setClassMatchPattern($pattern)
  {
    $this->pointcut->getClassMatcher()->setPattern($pattern);
  }
  
  public function setMethodMatchPattern($pattern)
  {
    $this->pointcut->getMethodMatcher()->setPattern($pattern);
  }
  
  public function setAdvice(Sabel_Aspect_Advice $advice)
  {
    $this->advice = $advice;
  }
  
  /**
   * implements Sabel_Aspect_PointcutAdvisor interface
   */
  public function getAdvice()
  {
    return $this->advice;
  }
  
  /**
   * implements Sabel_Aspect_PointcutAdvisor interface
   */
  public function getPointcut()
  {
    return $this->pointcut;
  }
  
  /**
   * implements Sabel_Aspect_PointcutAdvisor interface
   */
  public function isPerInstance()
  {
    
  }
}