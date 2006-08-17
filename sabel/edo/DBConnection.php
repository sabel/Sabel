<?php

class Sabel_Edo_DBConnection
{
  private static $connList = array();

  public static function addConnection($connectName ,$useEdoDriver, $connection)
  {
    if ($useEdoDriver === 'pdo') {
      if(is_array($connection)) {
        $dsn = $connection['dsn'];
        
        $list['conn']   = new PDO($dsn, $connection['user'], $connection['pass']);
        $list['driver'] = $useEdoDriver;
        $list['db']     = substr($dsn, 0, strpos($dsn, ':'));
        self::$connList[$connectName] = $list;
      } else {
        throw new Exception('DBConnection::addConnection() invalid Parameter. when use pdo, 3rd Argument must be array.');
      }
    } else {
      if(!is_array($connection)) {
        $list['conn']   = $connection;
        $list['driver'] = $useEdoDriver;
        $list['db']     = $useEdoDriver;
        self::$connList[$connectName] = $list; 
      } else {
        throw new Exception('DBConnection::addConnection() invalid Parameter. 3rd Argument must be string.');
      }
    }
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
