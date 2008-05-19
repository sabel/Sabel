<?php

/**
 * Sabel Container
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_Container_Aspect
{
  private $methods = array();
  private $aspects = array();
  
  public function apply($aspect)
  {
    $this->aspects[] = $aspect;
    return $this;
  }
  
  public function to($method)
  {
    $this->methods[] = $method;
    return $this;
  }
  
  public function getAspects()
  {
    return $this->aspects;
  }
  
  public function getMethods()
  {
    return $this->methods;
  }
}
