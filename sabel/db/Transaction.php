<?php

class Sabel_DB_Transaction
{
  private static $list   = array();
  private static $active = false;

  public static function begin($connectName, $connection, $driver)
  {
    if (!array_key_exists($connectName, self::$list)) {
      self::$list[$connectName]['conn']   = $connection;
      self::$list[$connectName]['driver'] = $driver;

      $driver->begin();
    }
  }

  public static function enableTransaction()
  {
    self::$active = true;
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
    foreach (self::$list as $connection)
      $connection['driver']->$method($connection['conn']);

    self::$list   = array();
    self::$active = false;
  }
}
