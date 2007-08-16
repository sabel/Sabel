<?php

/**
 * Sabel_DB_Config
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Config
{
  private static $initialized = false;
  private static $configs = array();

  public static function initialize()
  {
    if (self::$initialized) return;

    Sabel::fileUsing(RUN_BASE . "/config/connection.php", true);
    foreach (get_db_params() as $connectionName => $params) {
      self::regist($connectionName, $params);
    }

    self::$initialized = true;
  }

  public static function regist($connectionName, $params)
  {
    self::$configs[$connectionName] = $params;
  }

  public static function get($connectionName = null)
  {
    if ($connectionName === null) {
      return self::$configs;
    } else {
      return self::getConfig($connectionName);
    }
  }

  public static function loadDriver($connectionName)
  {
    $driverName = Sabel_DB_Config::getDriverName($connectionName);

    if (strpos($driverName, "pdo") === false) {
      $className = "Sabel_DB_" . ucfirst($driverName) . "_Driver";
    } else {
      list (, $db) = explode("-", $driverName);
      $className = "Sabel_DB_Pdo_Driver_" . ucfirst($db);
    }

    $driver = new $className();
    $driver->setConnectionName($connectionName);
    return $driver;
  }

  public static function getDB($connectionName)
  {
    return str_replace("pdo-", "", self::getDriverName($connectionName));
  }

  public static function getDriverName($connectionName)
  {
    $config = self::getConfig($connectionName);

    if (isset($config["driver"])) {
      return $config["driver"];
    } else {
      $e = new Sabel_DB_Exception_Config();
      throw $e->undefinedIndex("getDriverName", "driver");
    }
  }

  public static function getSchemaName($connectionName)
  {
    $drvName = self::getDriverName($connectionName);
    if (in_array($drvName, array("pdo-sqlite", "ibase"))) return null;

    $config = self::getConfig($connectionName);

    if (in_array($drvName, array("mysql", "mysqli", "pdo-mysql", "mssql"))) {
      return $config["database"];
    } elseif ($drvName === "oci") {
      return strtoupper($config["user"]);
    } elseif (isset($config["schema"])) {
      return $config["schema"];
    } elseif ($drvName === "pgsql" || $drvName === "pdo-pgsql") {
      return "public";
    } else {
      $e = new Sabel_DB_Exception_Config();
      throw $e->undefinedIndex("getSchemaName", "schema");
    }
  }

  private static function getConfig($connectionName)
  {
    if (isset(self::$configs[$connectionName])) {
      return self::$configs[$connectionName];
    } else {
      $e = new Sabel_DB_Exception_Config();
      throw $e->notFound($connectionName);
    }
  }
}
