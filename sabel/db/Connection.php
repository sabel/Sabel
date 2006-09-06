<?php

class Sabel_DB_Connection
{
  private static $connList = array();

  public static function addConnection($connectName, $params)
  {
    $driver = $params['driver'];

    if (!is_array($params))
      throw new Exception('invalid Parameter. 2rd Argument must be array.');

    if (strpos($driver, 'pdo-') !== false) {
      $db  = str_replace('pdo-', '', $driver);

      if ($db === 'sqlite') {
        $list['conn'] = new PDO("sqlite:{$params['database']}");
      } else {
        $dsn = "{$db}:host={$params['host']};dbname={$params['database']}";
        $list['conn'] = new PDO($dsn, $params['user'], $params['password']);
      }

      $list['driver'] = 'pdo';
      $list['db']     = $db;
    } else {
      $host     = $params['host'];
      $user     = $params['user'];
      $pass     = $params['password'];
      $database = $params['database'];

      if ($driver === 'mysql') {
        $host = (isset($params['port'])) ? $host . ':' . $params['port'] : $host;
        $list['conn'] = mysql_connect($host, $user, $pass);
        mysql_select_db($database, $list['conn']);
      } else if ($driver === 'pgsql') {
        $host = (isset($params['port'])) ? $host . ' port=' . $params['port'] : $host;
        $list['conn'] = pg_connect("host={$host} dbname={$database} user={$user} password={$pass}");
      } else if ($driver === 'firebird') {
        $host = $host . ':' . $database;
        $list['conn'] = ibase_connect($host, $user, $pass);
      }

      $list['driver'] = $driver;
      $list['db']     = $driver;
    }

    $list['schema'] = (isset($params['schema'])) ? $params['schema'] : null;
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
