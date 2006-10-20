<?php

/**
 * Sabel_DB_Transaction
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Transaction
{
  private static $list   = array();
  private static $active = false;

  public static function begin($connectName, $driver)
  {
    if (!array_key_exists($connectName, self::$list)) {
      $conn = Sabel_DB_Connection::getConnection($connectName);
      self::$list[$connectName]['conn']   = $conn;
      self::$list[$connectName]['driver'] = $driver;

      if (!is_null($result = $driver->begin($conn)))
        self::$list[$connectName]['conn'] = $result;

      self::$active = true;
    }
  }

  public static function isActive()
  {
    return self::$active;
  }

  public static function commit()
  {
    self::executeMethod('commit');
  }

  public static function rollback()
  {
    self::executeMethod('rollback');
  }

  private static function executeMethod($method)
  {
    if (count(self::$list) > 0) {
      foreach (self::$list as $connection) {
        $connection['driver']->$method($connection['conn']);
      }
      self::$list   = array();
      self::$active = false;
    }
  }
}
