<?php

/**
 * Sabel Object
 *
 * @category   core
 * @package    org.sabel.object
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Object
{
  public function hasMethod($name)
  {
    return (method_exists($this, $name));
  }
}