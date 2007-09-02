<?php

/**
 * Sabel Object
 *
 * @abstract
 * @category   core
 * @package    org.sabel.object
 * @author     Mori Reo <mori.reo@gmail.com>
 *             Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Object
{
  public final function hasMethod($name)
  {
    return (method_exists($this, $name));
  }
  
  public function getName()
  {
    return get_class($this);
  }
  
  public function __toString()
  {
    return $this->hashCode();
  }
  
  public function equals($object)
  {
    return ($this == $object);
  }
  
  public function hashCode()
  {
    return md5(serialize($this));
  }
  
  /**
   * an alias for __toString()
   */
  public final function toString()
  {
    return $this->__toString();
  }
}
