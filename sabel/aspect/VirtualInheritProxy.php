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
  private $parents   = array();
  
  public function inherit($parent)
  {
    $parentInstance = Container::create()->load($parent);
    $this->parents[] = $parentInstance;
    return $this;
  }
  
  protected function hasParent()
  {
    return (count($this->parents) > 0);
  }
  
  protected function isParentMethod($method)
  {
    $result = false;
    $parents = $this->parents;
    foreach ($parents as $parent) {
      if ($parent->hasMethod($method)) {
        $result = true;
        break;
      }
    }
    
    if ($result) {
      return $parent;
    } else {
      return $result;
    }
  }
  
  protected function beforeCallBefore($method, $arg)
  {
    if (($parent = $this->isParentMethod($method))) {
      return $parent->$method($arg);
    }
  }
}