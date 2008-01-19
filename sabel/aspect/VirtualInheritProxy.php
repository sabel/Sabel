<?php

/**
 * Sabel_Aspect_VirtualInheritProxy
 *
 * @category   Aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_VirtualInheritProxy extends Sabel_Aspect_Proxy
{
  private $parents = array();
  
  public function inherit($parent)
  {
    $this->parents[] = new $parent();
    return $this;
  }
  
  public function hasParent()
  {
    return (count($this->parents) > 0);
  }
  
  public function getParentByMethod($method)
  {
    foreach ($this->parents as $parent) {
      if (method_exists($parent, $method)) {
        return $parent;
      }
    }
    
    return null;
  }
  
  protected function isParentMethod($method)
  {
    foreach ($this->parents as $parent) {
      if ($parent->hasMethod($method)) {
        return true;
      }
    }
    
    return false;
  }
  
  protected function beforeCallBefore($method, $args)
  {
    if (($parent = $this->getParentByMethod($method))) {
      return call_user_func_array(array($parent, $method), $args);
    }
  }
}
