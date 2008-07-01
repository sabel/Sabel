<?php

class Sabel_Aspect_Factory
{
  private $weaver = null;
  private $advice = null;
  
  public function build($weaverClass, $targetClass, $adviceClasses)
  {
    $weaver = new $weaverClass($targetClass);
    
    if (!is_array($adviceClasses)) {
      $adviceClasses = array($adviceClasses);
    }
    
    $adviceClasses = array_reverse($adviceClasses);
    
    foreach ($adviceClasses as $adviceClass) {
      $advice = new $adviceClass();

      $this->weaver = $weaver;
      $this->advice = $advice;

      $reflection = new Sabel_Reflection_Class($advice);

      $annotatedAdvisor = $reflection->getAnnotation("advisor");

      if ($annotatedAdvisor === null) {
        $advisorClass = "Sabel_Aspect_RegexMatcherPointcutAdvisor";
      } else {
        $advisorClass = $annotatedAdvisor[0][0];
      }

      $annotatedInterceptor = $reflection->getAnnotation("interceptor");

      if ($annotatedInterceptor === null) {
        $interceptorClass = "Sabel_Aspect_PlainObjectAdviceInterceptor";
      } else {
        $interceptorClass = $annotatedInterceptor[0][0];
      }

      $annotatedClass = $reflection->getAnnotation("classMatch");

      if ($annotatedClass === null) {
        $annotatedClass = ".+";
      } else {
        $annotatedClass = $annotatedClass[0][0];
      }

      foreach ($reflection->getMethods() as $method) {
        $annotation = $method->getAnnotations();

        $advisor = new $advisorClass();
        $advisor->setClassMatchPattern("/{$annotatedClass}/");

        if (isset($annotation["before"])) {
          $before = $annotation["before"];

          $methodPattern = "/{$before[0][0]}/";
          $advisor->setMethodMatchPattern($methodPattern);

          $poInterceptor = new $interceptorClass($advice);
          $poInterceptor->setBeforeAdviceMethod($method->getName());

          $advisor->addAdvice($poInterceptor);
          $weaver->addAdvisor($advisor);
        } elseif (isset($annotation["after"])) {
          $after = $annotation["after"];

          $methodPattern = "/{$after[0][0]}/";
          $advisor->setMethodMatchPattern($methodPattern);

          $poInterceptor = new $interceptorClass($advice);
          $poInterceptor->setAfterAdviceMethod($method->getName());

          $advisor->addAdvice($poInterceptor);
          $weaver->addAdvisor($advisor);
        } elseif (isset($annotation["throws"])) {
          $throws = $annotation["throws"];

          $methodPattern = "/{$throws[0][0]}/";
          $advisor->setMethodMatchPattern($methodPattern);

          $poInterceptor = new $interceptorClass($advice);
          $poInterceptor->setThrowsAdviceMethod($method->getName());

          $advisor->addAdvice($poInterceptor);
          $weaver->addAdvisor($advisor);
        }
      }
    }
    
    return $weaver;
  }
  
  public function getAdvice()
  {
    return $this->advice;
  }
}

