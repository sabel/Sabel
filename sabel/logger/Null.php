<?php

/**
 * Sabel_Logger_Null
 *
 * @package    org.sabel.logger
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger_Null implements Sabel_Logger_Interface
{
  private static $instance = null;
  
  public static function singleton($option = null)
  {
    if (is_object(self::$instance)) {
      return self::$instance;
    } else {
      self::$instance = new self($option);
      return self::$instance;
    }
  }
  
  public function log($text, $level = LOG_INFO)
  {
  }
  
  public function __destruct()
  {
  }
}
