<?php

class Sabel_DB_Connection
{
  const MYSQL_SET_ENCODING = 'SET NAMES %s';
  const PGSQL_SET_ENCODING = 'SET CLIENT_ENCODING TO %s';

  protected static $parameters = array();
  protected static $connList   = array();

  public static function addConnection($connectName, $params)
  {
    self::$parameters[$connectName] = $params;
  }

  protected static function makeDatabaseLink($connectName)
  {
    if (!isset(self::$parameters[$connectName]))
      throw new Exception('Error: database parameters are not found: ' . $connectName);

    if (!is_array($params = self::$parameters[$connectName]))
      throw new Exception('invalid Parameter. parameters must be an array.');

    $driver = $params['driver'];

    if (strpos($driver, 'pdo-') !== false) {
      $db  = str_replace('pdo-', '', $driver);

      if ($db === 'sqlite') {
        $list['conn'] = new PDO("sqlite:{$params['database']}");
      } else {
        $dsn = "{$db}:host={$params['host']};dbname={$params['database']}";
        $list['conn'] = new PDO($dsn, $params['user'], $params['password']);
      }

      $list['drvName'] = 'pdo';
      $list['db']      = $db;
    } else {
      $host = $params['host'];
      $user = $params['user'];
      $pass = $params['password'];
      $dbs  = $params['database'];

      if ($driver === 'mysql') {
        $host = (isset($params['port'])) ? $host . ':' . $params['port'] : $host;
        $list['conn'] = mysql_connect($host, $user, $pass);
        mysql_select_db($dbs, $list['conn']);
      } else if ($driver === 'pgsql') {
        $host = (isset($params['port'])) ? $host . ' port=' . $params['port'] : $host;
        $list['conn'] = pg_connect("host={$host} dbname={$dbs} user={$user} password={$pass}");
      } else if ($driver === 'firebird') {
        $host = $host . ':' . $dbs;
        $list['conn'] = (isset($params['encoding'])) ? ibase_connect($host, $user, $pass, $params['encoding'])
                                                     : ibase_connect($host, $user, $pass);
      } else if ($driver === 'mssql') {
        $host = (isset($params['port'])) ? $host . ',' . $params['port'] : $host;
        $list['conn'] = mssql_connect($host, $user, $pass);
        mssql_select_db($dbs, $list['conn']);
      }

      $list['drvName'] = $driver;
      $list['db']      = $driver;
    }

    if (isset($params['encoding'])) {
      $db  = $list['db'];
      $enc = $params['encoding'];

      if ($list['drvName'] === 'pdo' && $db === 'mysql') {
        $list['conn']->exec(sprintf(self::MYSQL_SET_ENCODING, $enc));
      } else if ($list['drvName'] === 'pdo' && $db === 'pgsql') {
        $list['conn']->exec(sprintf(self::PGSQL_SET_ENCODING, $enc));
      } else if ($db === 'mysql') {
        mysql_query(sprintf(self::MYSQL_SET_ENCODING, $enc), $list['conn']);
      } else if ($db === 'pgsql') {
        pg_query($list['conn'], sprintf(self::PGSQL_SET_ENCODING, $enc));
      }
    }

    $list['schema'] = (isset($params['schema'])) ? $params['schema'] : null;
    self::$connList[$connectName] = $list;
    return $list['conn'];
  }

  public static function getDriver($connectName)
  {
    $conn    = self::getConnection($connectName);
    $drvName = self::getDriverName($connectName);

    switch ($drvName) {
      case 'pdo':
        $pdoDb = self::getDB($connectName);
        return new Sabel_DB_Driver_Pdo_Driver($conn, $pdoDb);
      default:
        $driver = 'Sabel_DB_Driver_Native_' . ucfirst($drvName);
        return new $driver($conn);
    }
  }

  public static function getConnection($connectName)
  {
    self::issetList($connectName, 'conn');
    return self::getValue($connectName, 'conn');
  }

  public static function getDriverName($connectName)
  {
    self::issetList($connectName, 'drvName');
    return self::getValue($connectName, 'drvName');
  }

  public static function getDB($connectName)
  {
    self::issetList($connectName, 'db');
    return self::getValue($connectName, 'db');
  }

  public static function getSchema($connectName)
  {
    $db = self::$connList[$connectName]['db'];
    if ($db !== 'sqlite' && $db !== 'firebird') return self::getValue($connectName, 'schema');
  }

  protected static function issetList($connectName, $key)
  {
    if (!isset(self::$connList[$connectName][$key])) self::makeDatabaseLink($connectName);
  }

  protected static function getValue($connectName, $key)
  {
    if (isset(self::$connList[$connectName][$key])) {
      return self::$connList[$connectName][$key];
    } else {
      throw new Exception("Error: value is not set:{$connectName} => {$key}");
    }
  }

  public static function close($connectName)
  {
    /*@todo
    $driver = self::getDBDriver($connectName);
    $driver->close();

    unset(self::$connList[$connectName]);
    */
  }

  public static function closeAll()
  {
    if (is_array(self::$connList)) {
      foreach (self::$connList as $list) {
        if (isset($list['driver'])) $list['driver']->close($list['conn']);
      }
      self::$connList = array();
    }
  }
}
