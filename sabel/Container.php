<?php

/**
 * Sabel Container
 *
 * @category   container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Container
{
  private static $components = array();
  
  public static function create($component)
  {
    $name = get_class($component);
    if (isset(self::$components[$name])) {
      return self::$components[$name];
    }
    
    $injector = new Sabel_Container_Injector($component);
    self::$components[$name] = $injector;
    
    return $injector;
  }
}
