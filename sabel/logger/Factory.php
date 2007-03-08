<?php

/**
 * Sabel_Logger_Factory
 *
 * @package    org.sabel.logger
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger_Factory
{
  public static function create($class, $option = null)
  {
    $className = "Sabel_Logger_" . ucfirst($class);
    return load($className, "Dependency_Config", true);
  }
}