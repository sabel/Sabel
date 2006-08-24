<?php

class Sabel_DB_Connection
{
  private static $connList = array();

  public static function addConnection($connectName ,$driver, $connection)
  {
    if ($driver === 'pdo') {
      if(!is_array($connection))
        throw new Exception('invalid Parameter. when use pdo, 3rd Argument must be array.');

      $dsn = $connection['dsn'];
      $db  = substr($dsn, 0, strpos($dsn, ':'));

      if ($db === 'sqlite') {
        $list['conn'] = new PDO($dsn);
      } else {
        $list['conn'] = new PDO($dsn, $connection['user'], $connection['pass']);
      }

      $list['driver'] = $driver;
      $list['db']     = $db;
    } else {
      if(is_array($connection))
        throw new Exception('invalid Parameter. 3rd Argument must be string.');

      $list['conn']   = $connection;
      $list['driver'] = $driver;
      $list['db']     = $driver;
    }
    self::$connList[$connectName] = $list;
  }

  public static function getConnection($connectName)
  {
    if (isset(self::$connList[$connectName])) {
      return self::$connList[$connectName]['conn'];
    }
  }

  public static function getDriver($connectName)
  {
    if (isset(self::$connList[$connectName])) {
      return self::$connList[$connectName]['driver'];
    }
  }

  public static function getDB($connectName)
  {
    if (isset(self::$connList[$connectName])) {
      return self::$connList[$connectName]['db'];
    }
  }
}
