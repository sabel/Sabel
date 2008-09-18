<?php

/**
 * MethodBeforeAdvice interceptor
 *
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Aspect_Weaver_Abstract implements Sabel_Aspect_Weaver
{
  protected $target  = null;
  protected $advisor = array();
  
  public function __construct($target = null)
  {
    if ($target !== null) {
      $this->target = $target;  
    }
  }
  
  public function addAdvisor($advisor, $position = null)
  {
    if ($position === null) {
      $position = count($this->advisor);
    }
    
    $this->advisor[$position] = $advisor;
  }
  
  /**
   * @param object $target
   */
  public function setTarget($target)
  {
    if (class_exists($target)) {
      $this->target = $target;  
    } else {
      throw new Sabel_Exception_Runtime("target must be exist class. {$target} not found");
    }
  }
}