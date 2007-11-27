<?php

/**
 * Sabel Container
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_Container_Aspect
{
  private $methods = array();
  private $aspects = array();
  
  public function method($method)
  {
    $this->methods[] = $method;
    return $this;
  }
  
  public function apply($aspect)
  {
    $this->aspects[] = $aspect;    
    return $this;
  }
  
  public function getAppliedAspects()
  {
    return $this->aspects;
  }
  
  public function getAppliedMethods()
  {
    return $this->methods;
  }
}
