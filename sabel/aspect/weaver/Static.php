<?php

/**
 * Static Weaver
 *
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Weaver_Static extends Sabel_Aspect_Weaver_Abstract
{
  public function getProxy()
  {
    if ($this->target === null) {
      throw new Sabel_Exception_Runtime("must be set target class");
    }
    
    if (!is_object($this->target)) {
      if (class_exists($this->target)) {
        $this->target = new $this->target();
      }
    }
    
    $adviced    = new Sabel_Aspect_Adviced();
    $reflection = new Sabel_Reflection_Class($this->target);
    
    foreach ($this->advisor as $advisor) {
      if (!$advisor instanceof Sabel_Aspect_Advisor) {
        throw new Sabel_Exception_Runtime("advisor must be implements Sabel_Aspect_Advisor");
      }
        
      $pointcut = $advisor->getPointcut();
      
      if (!$pointcut instanceof Sabel_Aspect_Pointcut) {
        throw new Sabel_Exception_Runtime("pointcut must be Sabel_Aspect_Pointcut");
      }
      
      $pointcuts = new Sabel_Aspect_DefaultPointcuts();
      
      foreach ($reflection->getMethods() as $method) {
        if ($pointcuts->matches($pointcut, $method->getName(), $this->target)) {
          $adviced->addAdvices($method->getName(), $advisor->getAdvice());
        }
      }
    }
    
    if ($adviced->hasAdvices()) {
      $proxy = new Sabel_Aspect_Proxy_Static($this->target);
      $proxy->__setAdviced($adviced);
      
      return $proxy;
    } else {
      // no match found. return a raw target object.
      return $this->target;
    }
  }
}