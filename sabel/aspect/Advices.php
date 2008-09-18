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
    if ($advice instanceof Sabel_Aspect_Advice_MethodBefore) {
      $this->advices[] = new Sabel_Aspect_Advice_MethodBeforeInterceptor($advice);
    } elseif ($advice instanceof Sabel_Aspect_Advice_MethodAfterReturning) {
      $this->advices[] = new Sabel_Aspect_Advice_MethodAfterReturningInterceptor($advice);
    } elseif ($advice instanceof Sabel_Aspect_Advice_MethodAfter) {
      $this->advices[] = new Sabel_Aspect_Advice_MethodAfterInterceptor($advice);
    } elseif ($advice instanceof Sabel_Aspect_Advice_MethodThrows) {
      $this->advices[] = new Sabel_Aspect_Advice_MethodThrowsInterceptor($advice);
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