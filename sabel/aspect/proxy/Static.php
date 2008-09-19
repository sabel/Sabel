<?php

/**
 * Static Proxy
 *
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Proxy_Static extends Sabel_Aspect_Proxy_Abstract
{
  private $adviced = null;
  
  protected function __setupInvocation()
  {
    $this->invocation = new Sabel_Aspect_DefaultMethodInvocation($this, $this->target);
  }
  
  public function __setAdviced($adviced)
  {
    $this->adviced = $adviced;
  }
  
  public function __call($method, $arg)
  {
    $this->invocation->reset($method, $arg);
    
    if ($this->adviced->hasAdvice($method)) {
      $this->invocation->setAdvices($this->adviced->getAdvice($method));
    }
    
    return $this->invocation->proceed();
  }
}