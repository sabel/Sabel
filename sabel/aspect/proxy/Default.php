<?php

/**
 * Dynamic Proxy
 *
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Proxy_Default extends Sabel_Aspect_Proxy_Abstract
{
  protected function __setupInvocation()
  {
    $this->invocation = new Sabel_Aspect_DefaultMethodInvocation($this, $this->target);
  }
  
  public function __call($method, $arg)
  {
    $reflection = new Sabel_Reflection_Class($this->target);
    
    if ($this->checkTargetMethod && !$reflection->hasMethod($method)) {
      throw new Sabel_Aspect_Exception_MethodNotFound($method . " not found");
    }
    
    $this->invocation->reset($method, $arg);
    
    $advices = array();
    
    $pointcuts = new Sabel_Aspect_DefaultPointcuts();
    
    foreach ($this->advisor as $advisor) {
      $pointcut = $advisor->getPointcut();
      
      if (!$pointcut instanceof Sabel_Aspect_Pointcut)
        throw new Sabel_Exception_Runtime("pointcut must be Sabel_Aspect_Pointcut");
      
      if ($pointcuts->matches($pointcut, $method, $this->target)) {
        $advice = $advisor->getAdvice();
        
        if (is_array($advice)) {
          $advices = array_merge($advice, $advices);
        } else {
          $advices[] = $advice;
        }
      }
    }
    
    if (count($advices) >= 1) {
      $this->invocation->setAdvices($advices);
    }
    
    return $this->invocation->proceed();
  }
}