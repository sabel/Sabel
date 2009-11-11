<?php

class Sabel_Aspect_RegexFactory
{
  private static $reflectionCache = array();
  
  private $weaver = null;
  private $advice = null;
  
  private $advisorClass     = "Sabel_Aspect_Advisor_RegexMatcherPointcut";
  private $annotatedClass   = ".+";
  private $interceptorClass = "Sabel_Aspect_Interceptor_PlainObjectAdvice";
  
  private $methodPatterns = array();
  
  private $types = array("before", "after", "around", "throws");
  
  public static function create()
  {
    return new self();
  }
  
  public function getAdvice()
  {
    return $this->advice;
  }
  
  public function build($targetClass, $adviceClasses)
  {
    $this->weaver = new Sabel_Aspect_Weaver($targetClass);
    
    if (is_array($adviceClasses)) {
      $this->advices = array();
      $adviceClasses = array_reverse($adviceClasses);
      
      foreach ($adviceClasses as $adviceClass) {
        $this->_build($adviceClass);
      }
    } else {
      $this->_build($adviceClasses);
    }
    
    return $this->weaver;
  }
  
  private function _build($adviceClass)
  {
    $this->advice = $advice = new $adviceClass();
    
    if (isset(self::$reflectionCache[$adviceClass])) {
      $reflection = self::$reflectionCache[$adviceClass];
    } else {
      $reflection = new Sabel_Reflection_Class($advice);
      self::$reflectionCache[$adviceClass] = $reflection;
    }
            
    $annotatedAdvisor = $reflection->getAnnotation("advisor");
    if ($annotatedAdvisor !== null) {
      $this->advisorClass = $annotatedAdvisor[0][0];
    }
    
    $annotatedInterceptor = $reflection->getAnnotation("interceptor");
    if ($annotatedInterceptor !== null) {
      $this->interceptorClass = $annotatedInterceptor[0][0];
    }
    
    $annotatedClass = $reflection->getAnnotation("classMatch");
    if ($annotatedClass !== null) {
      $this->annotatedClass = $annotatedClass[0][0];
    }
    
    foreach ($reflection->getMethods() as $method) {
      $this->addToAdvisor($method, $advice);
    }    
  }
  
  private function addToAdvisor($method, $advice)
  {
    $annotation = $method->getAnnotations();
    
    $type = null;
    foreach ($this->types as $cType) {
      if (isset($annotation[$cType])) {
        $type = $cType;
      }
    }
    if ($type === null) return;
    
    $pattern = $annotation[$type][0][0];
    $methodPattern = "/{$pattern}/";
    
    if (isset($this->methodPatterns[$methodPattern])) {
      $advisor = $this->methodPatterns[$methodPattern];
    } else {
      $advisorClass   = $this->advisorClass;
      $annotatedClass = $this->annotatedClass;
      
      if (!class_exists($advisorClass, true)) {
        throw new Sabel_Exception_ClassNotFound($advisorClass);
      }
      
      $advisor = new $advisorClass();
      $advisor->setClassMatchPattern("/{$annotatedClass}/");
      $advisor->setMethodMatchPattern($methodPattern);
      $this->methodPatterns[$methodPattern] = $advisor;
      $this->weaver->addAdvisor($advisor);
    }
    
    $interceptorClass = $this->interceptorClass;
    $poInterceptor = new $interceptorClass($advice);
    
    $setMethod = "set" . ucfirst($type) . "AdviceMethod";
    $poInterceptor->$setMethod($method->getName());
    
    $advisor->addAdvice($poInterceptor);
  }
}
