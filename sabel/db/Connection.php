<?php

/**
 * Sabel_DB_Connection
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
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

    $drvName = $params['driver'];

    if (strpos($drvName, 'pdo-') === 0) {
      $type = 'pdo';
      $db   = str_replace('pdo-', '', $drvName);

      if ($db === 'sqlite') {
        $list['conn'] = new PDO("sqlite:{$params['database']}");
      } else {
        $dsn = "{$db}:host={$params['host']};dbname={$params['database']}";
        $list['conn'] = new PDO($dsn, $params['user'], $params['password']);
      }

      $list['db'] = $db;
    } else {
      $type = 'native';
      $host = $params['host'];
      $user = $params['user'];
      $pass = $params['password'];
      $dbs  = $params['database'];

      if ($drvName === 'mysql') {
        $host = (isset($params['port'])) ? $host . ':' . $params['port'] : $host;
        $list['conn'] = mysql_connect($host, $user, $pass);
        mysql_select_db($dbs, $list['conn']);
      } else if ($drvName === 'pgsql') {
        $host = (isset($params['port'])) ? $host . ' port=' . $params['port'] : $host;
        $list['conn'] = pg_connect("host={$host} dbname={$dbs} user={$user} password={$pass}");
      } else if ($drvName === 'firebird') {
        $host = $host . ':' . $dbs;
        $list['conn'] = (isset($params['encoding'])) ? ibase_connect($host, $user, $pass, $params['encoding'])
                                                     : ibase_connect($host, $user, $pass);
      } else if ($drvName === 'mssql') {
        $host = (isset($params['port'])) ? $host . ',' . $params['port'] : $host;
        $list['conn'] = mssql_connect($host, $user, $pass);
        mssql_select_db($dbs, $list['conn']);
      }

      $list['db'] = $drvName;
    }

    if (isset($params['encoding'])) {
      $db  = $list['db'];
      $enc = $params['encoding'];

      if ($type === 'pdo' && $db === 'mysql') {
        $list['conn']->exec(sprintf(self::MYSQL_SET_ENCODING, $enc));
      } else if ($type === 'pdo' && $db === 'pgsql') {
        $list['conn']->exec(sprintf(self::PGSQL_SET_ENCODING, $enc));
      } else if ($db === 'mysql') {
        mysql_query(sprintf(self::MYSQL_SET_ENCODING, $enc), $list['conn']);
      } else if ($db === 'pgsql') {
        pg_query($list['conn'], sprintf(self::PGSQL_SET_ENCODING, $enc));
      }
    }

    $list['schema']  = (isset($params['schema'])) ? $params['schema'] : null;
    self::$connList[$connectName] = $list;
    return $list['conn'];
  }

  public static function getDriver($conName)
  {
    $conn    = self::getConnection($conName);
    $drvName = self::getDriverName($conName);

    if (strpos($drvName, 'pdo') === 0) {
      $driver  = new Sabel_DB_Driver_Pdo($conn, self::getDB($conName));
    } else {
      $clsName = 'Sabel_DB_Driver_' . ucfirst($drvName);
      $driver  = new $clsName($conn);
    }

    self::$connList[$conName]['driver'] = $driver;
    return $driver;
  }

  public static function getConnection($conName)
  {
    self::createDBLink($conName, 'conn');
    return self::getValue($conName, 'conn');
  }

  public static function getDB($conName)
  {
    self::createDBLink($conName, 'db');
    return self::getValue($conName, 'db');
  }

  protected static function createDBLink($conName, $key)
  {
    if (!isset(self::$connList[$conName][$key])) self::makeDatabaseLink($conName);
  }

  protected static function getValue($conName, $key)
  {
    if (isset(self::$connList[$conName][$key])) {
      return self::$connList[$conName][$key];
    } else {
      throw new Exception("Error: value is not set:{$conName} => {$key}");
    }
  }

  public static function getDriverName($conName)
  {
    if (isset(self::$parameters[$conName]['driver'])) {
      return self::$parameters[$conName]['driver'];
    } else {
      $msg = "Sabel_DB_Connection::getDriverName() value is not set: $conName";
      throw new Exception($msg);
    }
  }

  public static function getSchema($conName)
  {
    $drvName = self::getDriverName($conName);
    if (in_array($drvName, array('pdo-sqlite', 'firebird'))) return null;

    if (isset(self::$parameters[$conName]['schema'])) {
      return self::$parameters[$conName]['schema'];
    } else {
      $msg = "Sabel_DB_Connection::getSchema() value is not set: $conName";
      throw new Exception($msg);
    }
  }

  public static function close($conName)
  {
    if (!isset(self::$connList[$conName])) return null;

    $list = self::$connList[$conName];
    if (isset($list['driver'])) $list['driver']->close($list['conn']);
    unset(self::$connList[$conName]);
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
