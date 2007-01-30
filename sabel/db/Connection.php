<?php

/**
 * Sabel_DB_Connection
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Connection
{
  const SET_ENCODING = 'SET NAMES %s';

  protected static $isInit     = false;
  protected static $parameters = array();
  protected static $connList   = array();

  public static function initialize()
  {
    if (self::$isInit) return null;

    if (!defined('TEST_CASE')) {
      Sabel::fileUsing(RUN_BASE . '/config/database.php', true);
      foreach (get_db_params() as $connectName => $params) {
        self::addConnection($connectName, $params);
      }
    }

    self::$isInit = true;
  }

  public static function setInitFlag($bool)
  {
    if (!is_bool($bool)) {
      throw new Exception('Error:setInit() invalid parameter. argument should be a boolean.');
    }

    self::$isInit = $bool;
  }

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
      $type    = 'pdo';
      $drvName = str_replace('pdo-', '', $drvName);

      if ($drvName === 'sqlite') {
        $list['conn'] = new PDO("sqlite:{$params['database']}");
      } else {
        $dsn = "{$drvName}:host={$params['host']};dbname={$params['database']}";
        if (isset($params['port'])) $dsn .= ";port={$params['port']}";
        $list['conn'] = new PDO($dsn, $params['user'], $params['password']);
      }
    } else {
      $type = 'native';
      $host = $params['host'];
      $user = $params['user'];
      $pass = $params['password'];
      $dbs  = $params['database'];

      switch ($drvName) {
        case 'mysql':
          $host = (isset($params['port'])) ? $host . ':' . $params['port'] : $host;
          $list['conn'] = mysql_connect($host, $user, $pass);
          mysql_select_db($dbs, $list['conn']);
          break;
        case 'pgsql':
          $host = (isset($params['port'])) ? $host . ' port=' . $params['port'] : $host;
          $list['conn'] = pg_connect("host={$host} dbname={$dbs} user={$user} password={$pass}");
          break;
        case 'firebird':
          $host = $host . ':' . $dbs;
          $enc  = (isset($params['encoding'])) ? $params['encoding'] : null;
          $list['conn'] = ibase_connect($host, $user, $pass, $enc);
          break;
        case'mssql':
          $host = (isset($params['port'])) ? $host . ',' . $params['port'] : $host;
          $list['conn'] = mssql_connect($host, $user, $pass);
          mssql_select_db($dbs, $list['conn']);
          break;
      }
    }

    if (isset($params['encoding'])) {
      $db  = $drvName;
      $enc = $params['encoding'];

      if ($type === 'pdo') {
        $list['conn']->exec(sprintf(self::SET_ENCODING, $enc));
      } elseif ($db === 'mysql') {
        mysql_query(sprintf(self::SET_ENCODING, $enc), $list['conn']);
      } elseif ($db === 'pgsql') {
        pg_query($list['conn'], sprintf(self::SET_ENCODING, $enc));
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

    if (strpos($drvName, 'pdo-') === 0) {
      $driver  = Sabel::load('Sabel_DB_Pdo_Driver', $conn, self::getDB($conName));
    } else {
      $clsName = 'Sabel_DB_' . ucfirst($drvName) . '_Driver';
      $driver  = Sabel::load($clsName, $conn);
    }

    self::$connList[$conName]['driver'] = $driver;
    return $driver;
  }

  public static function getConnection($conName)
  {
    if (!isset(self::$connList[$conName]['conn'])) {
      self::makeDatabaseLink($conName);
    }

    if (isset(self::$connList[$conName]['conn'])) {
      return self::$connList[$conName]['conn'];
    } else {
      throw new Exception("Error: failed in connect to the database.");
    }
  }

  public static function getDB($conName)
  {
    return str_replace('pdo-', '', self::getDriverName($conName));
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
