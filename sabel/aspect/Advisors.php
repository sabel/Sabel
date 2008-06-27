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

class Sabel_Aspect_Advices
{
  private $advices = array();
  
  public function addAdvice(Sabel_Aspect_Advice $advice)
  {
    if ($advice instanceof Sabel_Aspect_MethodBeforeAdvice) {
      $this->advices[] = new Sabel_Aspect_MethodBeforeAdviceInterceptor($advice);
    } elseif ($advice instanceof Sabel_Aspect_MethodInterceptor) {
      $this->advices[] = $advice;
    }
  }
  
  public function getAdvices()
  {
    return $this->advices;
  }
  
  public function toArray()
  {
    return $this->advices;
  }
  
  public function __toString()
  {
    $buffer = array();
    
    foreach ($this->advices as $advice) {
      $buffer[] =(string) $advice;
    }
    
    return join("\n", $buffer);
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
  private $advices = array();
  
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
  
  public function setAdvice($advice)
  {
    if (is_array($advice)) {
      $this->advice = $advice;
    } else {
      $this->advice = array($advice);
    }
  }
  
  public function addAdvice(Sabel_Aspect_Advice $advice)
  {
    $this->advice[] = $advice;
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