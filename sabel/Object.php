<?php

/**
 * Sabel Object
 *
 * @abstract
 * @category   core
 * @package    org.sabel.object
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Object
{
  public final function hasMethod($name)
  {
    return (method_exists($this, $name));
  }
  
  public final function getName()
  {
    return get_class($this);
  }
}
