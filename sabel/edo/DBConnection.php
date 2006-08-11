<?php

class Sabel_Edo_DBConnection
{
  private static $connList = array();

  public static function addConnection($connectName ,$useEdoDriver, $connection)
  {
    if ($useEdoDriver == 'pdo') {
      if(!is_array($connection)) {
        throw new Exception('DBConnection::addConnection() invalid Parameter. when use pdo, 3rd Argument must be array.');
      } else {
        $splited = split(':', $connection['dsn']);

        $conn = new PDO($connection['dsn'], $connection['user'], $connection['pass']);
        self::$connList[$connectName]['conn']   = $conn;
        self::$connList[$connectName]['driver'] = $useEdoDriver;
        self::$connList[$connectName]['db']     = $splited[0];
      }
    } else {
      if(is_array($connection)) {
        throw new Exception('DBConnection::addConnection() invalid Parameter. 3rd Argument must be string.');
      } else {
        self::$connList[$connectName]['conn']   = $connection;
        self::$connList[$connectName]['driver'] = $useEdoDriver;
        self::$connList[$connectName]['db']     = $useEdoDriver;
      }
    }
  }

  public static function getConnection($connectName)
  {
    return self::$connList[$connectName]['conn'];
  }

  public static function getEdoDriver($connectName)
  {
    return self::$connList[$connectName]['driver'];
  }

  public static function getDB($connectName)
  {
    return self::$connList[$connectName]['db'];
  }
}
