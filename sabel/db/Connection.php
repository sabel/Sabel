<?php

class Sabel_DB_Connection
{
  private static $connList = array();

  public static function addConnection($connectName ,$driver, $connection)
  {
    if ($driver === 'pdo') {
      if(is_array($connection)) {
        $dsn = $connection['dsn'];

        $list['conn']   = new PDO($dsn, $connection['user'], $connection['pass']);
        $list['driver'] = $driver;
        $list['db']     = substr($dsn, 0, strpos($dsn, ':'));
      } else {
        throw new Exception('DBConnection::addConnection() invalid Parameter. when use pdo, 3rd Argument must be array.');
      }
    } else {
      if(!is_array($connection)) {
        $list['conn']   = $connection;
        $list['driver'] = $driver;
        $list['db']     = $driver;
      } else {
        throw new Exception('DBConnection::addConnection() invalid Parameter. 3rd Argument must be string.');
      }
    }
    self::$connList[$connectName] = $list;
  }

  public static function getConnection($connectName)
  {
    return self::$connList[$connectName]['conn'];
  }

  public static function getDriver($connectName)
  {
    return self::$connList[$connectName]['driver'];
  }

  public static function getDB($connectName)
  {
    return self::$connList[$connectName]['db'];
  }
}
