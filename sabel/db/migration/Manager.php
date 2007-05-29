<?php

/**
 * Sabel_DB_Migration_Manager
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Manager
{
  private static $accessor = null;
  private static $driver   = null;

  public static function setAccessor($accessor)
  {
    self::$accessor = $accessor;
  }

  public static function getAccessor()
  {
    return self::$accessor;
  }

  public static function setDriver($driver)
  {
    self::$driver = $driver;
  }

  public static function getDriver()
  {
    return self::$driver;
  }
}
