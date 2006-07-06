<?php

class Sabel_Edo_DBConnection
{
  private static $connList = array();

  public static function addConnection($owner ,$useEdo, $connection)
  {
    if ($useEdo == 'pdo') {
      if(!is_array($connection)) {
        throw new Exception('DBConnection::addConnection() invalid Parameter. when use pdo, 3rd Argument must be array.');
      } else {
        $dsn  = $connection['dsn']; 
        $user = $connection['user']; 
        $pass = $connection['pass'];
        
        $splited = split(':', $dsn);

        $conn = new PDO($dsn, $user, $pass);
        self::$connList[$owner]['pdo'] = $conn;
        self::$connList[$owner]['db']  = $splited[0];
      }
    } else {
      if(is_array($connection)) {
        throw new Exception('DBConnection::addConnection() invalid Parameter. 3rd Argument must be string.');
      } else {
        if ($useEdo == 'pgsql') {
          $conn = pg_connect($connection);
          self::$connList[$owner]['pgsql'] = $conn;
        } elseif ($useEdo == 'mysql') {
          $conn = mysql_connect($connection);
          self::$connList[$owner]['mysql'] = $conn;
        } else {
          throw new Exception('DBConnection::addConnection() invalid Parameter. EDO hasn\'t '.$useEdo);
        }
      }
    }
  }

  public static function getConnection($owner, $useEdo)
  {
    return self::$connList[$owner][$useEdo];
  }

  public static function getPdoDB($owner)
  {
    return self::$connList[$owner]['db'];
  }
}

?>
