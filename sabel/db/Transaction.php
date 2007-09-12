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
  private static $active = false;
  private static $transactions = array();

  public static function activate()
  {
    self::$active = true;
  }

  public static function isActive($connectionName = null)
  {
    if ($connectionName === null) {
      return self::$active;
    } else {
      return (isset(self::$transactions[$connectionName]));
    }
  }

  public static function getConnection($connectionName)
  {
    $ts = self::$transactions;
    return (isset($ts[$connectionName]["conn"])) ? $ts[$connectionName]["conn"] : null;
  }

  public static function begin($driver, $connectionName)
  {
    self::$transactions[$connectionName]["conn"]   = $driver->begin();
    self::$transactions[$connectionName]["driver"] = $driver;

    self::$active = true;
  }

  public static function commit()
  {
    self::finish("commit");
  }

  public static function rollback()
  {
    self::finish("rollback");
  }

  private static function finish($method)
  {
    if (self::$active) {
      foreach (self::$transactions as $trans) {
        $trans["driver"]->$method();
      }

      self::clear();
    }
  }

  public static function clear()
  {
    self::$active = false;
    self::$transactions = array();
  }
}
