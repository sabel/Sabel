<?php

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
      return true;
    } else {
      return false;
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
    foreach (self::$list as $connection) {
      $connection['driver']->$method($connection['conn']);
    }

    self::$list   = array();
    self::$active = false;
  }
}
