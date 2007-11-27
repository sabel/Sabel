<?php

/**
 * Sabel_Logger_Factory
 *
 * @category   Logger
 * @package    org.sabel.logger
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger_Factory
{
  public static function create($class = null, $option = null)
  {
    if (defined("ENVIRONMENT")) {
      if (ENVIRONMENT === PRODUCTION) {
        return new Sabel_Logger_Null();
      }
    }
    
    if ($class === null) {
      $class = "File";
    }
    
    $className = "Sabel_Logger_" . ucfirst($class);
    return new $className;
  }
}
