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