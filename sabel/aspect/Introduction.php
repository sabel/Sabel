<?php

interface Sabel_Aspect_IntroductionAdvisor extends Sabel_Aspect_Advisor
{
}

interface Sabel_Aspect_IntroductionInterceptor extends Sabel_Aspect_MethodInterceptor
{
}

class Sabel_Aspect_DelegatingIntroductionInterceptor implements Sabel_Aspect_IntroductionInterceptor
{
  public function invoke(Sabel_Aspect_MethodInvocation $invocation)
  {
    return $invocation->proceed();
  }
}

class Sabel_Aspect_DefaultIntroductionAdvisor implements Sabel_Aspect_IntroductionAdvisor
{
  private $advice = null;
  
  public function __construct($advice)
  {
    $this->advice = $advice;
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
    return new TrueMatchPointcut();
  }
}