<?php

class Sabel_DB_Connection
{
  private static $connList = array();

  public static function addConnection($connectName ,$driver, $connection, $schema = null)
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
      $list['schema'] = $schema;
    } else {
      if(is_array($connection))
        throw new Exception('invalid Parameter. 3rd Argument must be string.');

      $list['conn']   = $connection;
      $list['driver'] = $driver;
      $list['db']     = $driver;
      $list['schema'] = $schema;
    }
    self::$connList[$connectName] = $list;
  }

  public static function getConnection($connectName)
  {
    return self::getValue($connectName, 'conn');
  }

  public static function getDriver($connectName)
  {
    return self::getValue($connectName, 'driver');
  }

  public static function getDB($connectName)
  {
    return self::getValue($connectName, 'db');
  }

  public static function getSchema($connectName)
  {
    return self::getValue($connectName, 'schema');
  }

  protected static function getValue($connectName, $key)
  {
    if (isset(self::$connList[$connectName])) {
      return self::$connList[$connectName][$key];
    }
  }
}
