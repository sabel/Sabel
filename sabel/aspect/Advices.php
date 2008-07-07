<?php

/**
 * Advices
 *
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Advices
{
  private $advices = array();
  
  public function addAdvice(Sabel_Aspect_Advice $advice)
  {
    if ($advice instanceof Sabel_Aspect_MethodBeforeAdvice) {
      $this->advices[] = new Sabel_Aspect_MethodBeforeAdviceInterceptor($advice);
    } elseif ($advice instanceof Sabel_Aspect_MethodAfterReturningAdvice) {
      $this->advices[] = new Sabel_Aspect_MethodAfterReturningAdviceInterceptor($advice);
    } elseif ($advice instanceof Sabel_Aspect_MethodAfterAdvice) {
      $this->advices[] = new Sabel_Aspect_MethodAfterAdviceInterceptor($advice);
    } elseif ($advice instanceof Sabel_Aspect_MethodThrowsAdvice) {
      $this->advices[] = new Sabel_Aspect_MethodThrowsAdviceInterceptor($advice);
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