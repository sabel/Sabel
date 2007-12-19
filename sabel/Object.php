<?php

/**
 * Sabel Object
 *
 * @abstract
 * @category   Core
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
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
    return sha1(serialize($this));
  }
  
  public function getReflection()
  {
    return new Sabel_Reflection_Class($this);
  }
  
  /**
   * an alias for __toString()
   */
  public final function toString()
  {
    return $this->__toString();
  }
}
