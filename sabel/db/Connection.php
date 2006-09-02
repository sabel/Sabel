<?php

class Sabel_DB_Connection
{
  private static $connList = array();

  public static function addConnection($connectName, $params)
  {
    $driver = $params['driver'];

    if (!is_array($params))
      throw new Exception('invalid Parameter. 2rd Argument must be array.');

    if (stripos($driver, 'pdo-') !== false) {
      $db  = str_replace('pdo-', '', $driver);
      $dsn = "{$db}:host={$params['host']};dbname={$params['database']}";

      if ($db === 'sqlite') {
        $list['conn'] = new PDO($dsn);
      } else {
        $list['conn'] = new PDO($dsn, $params['user'], $params['pass']);
      }

      $list['driver'] = 'pdo';
      $list['db']     = $db;
    } else {
      if ($driver === 'mysql') {
        $host = (isset($params['port'])) ? $params['host'] . ':' . $params['port'] : $params['host'];
        $list['conn'] = mysql_connect($host, $params['user'], $params['pass']);
        mysql_select_db($params['database'], $list['conn']);
      } else if ($driver === 'pgsql') {
        $host = (isset($params['port'])) ? $params['host'] . ' port=' . $params['port'] : $params['host'];
        $list['conn'] = pg_connect("host={$host} dbname={$params['database']} user={$params['user']} password={$params['pass']}");
      } else if ($driver === 'firebird') {
        $host = $params['host'] . ':' . $params['database'];
        $list['conn'] = ibase_connect($host, $params['user'], $params['pass']);
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
