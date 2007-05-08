<?php

/**
 * Sabel Container
 *
 * @category   container
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Container
{
  public static function injector($component)
  {
    return new Sabel_Container_Injector($component);
  }
}
