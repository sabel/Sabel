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
  const SET_ENCODING = "SET NAMES %s";

  private static $connections = array();

  public static function get($connectionName)
  {
    if (!isset(self::$connections[$connectionName])) {
      self::connect($connectionName);
    }

    return self::$connections[$connectionName];
  }

  protected static function connect($connectionName)
  {
    $error   = "";
    $params  = Sabel_DB_Config::get($connectionName);
    $drvName = $params["driver"];

    if (strpos($drvName, "pdo-") === 0) {
      $drvName = str_replace("pdo-", "", $drvName);
      list ($conn, $error) = self::pdoConnect($drvName, $params);
    } else {
      $currentLevel = error_reporting(0);

      switch ($drvName) {
        case "mysql":
          list ($conn, $error) = self::mysqlConnect($params);
          break;

        case "pgsql":
          list ($conn, $error) = self::pgsqlConnect($params);
          break;

        case "ibase":
          list ($conn, $error) = self::ibaseConnect($params);
          break;

        case "oci":
          list ($conn, $error) = self::ociConnect($params);
          break;

        case "mssql":
          list ($conn, $error) = self::mssqlConnect($params);
          break;
      }

      error_reporting($currentLevel);
    }

    if ($conn) {
      self::$connections[$connectionName] = $conn;
    } else {
      self::error($connectionName, $error, $params);
    }
  }

  private static function pdoConnect($name, $params)
  {
    $error = "";

    try {
      if ($name === "sqlite") {
        $conn = new PDO("sqlite:" . $params["database"]);
      } else {
        $dsn = "{$name}:host={$params["host"]};dbname={$params["database"]}";
        if (isset($params["port"])) $dsn .= ";port={$params["port"]}";
        $conn = new PDO($dsn, $params["user"], $params["password"]);
      }

      if (isset($params["encoding"])) {
        $conn->exec(sprintf(self::SET_ENCODING, $params["encoding"]));
      }

      return array($conn, "");
    } catch (PDOException $e) {
      return array(false, $e->getMessage());
    }
  }

  private static function mysqlConnect($params)
  {
    $host = $params["host"];

    $host = (isset($params["port"])) ? $host . ":" . $params["port"] : $host;
    $conn = mysql_connect($host, $params["user"], $params["password"]);

    if ($conn) {
      mysql_select_db($params["database"], $conn);

      if (isset($params["encoding"])) {
        mysql_query(sprintf(self::SET_ENCODING, $params["encoding"]), $conn);
      }

      return array($conn, "");
    } else {
      return array($conn, mysql_error());
    }
  }

  private static function pgsqlConnect($params)
  {
    $host = $params["host"];
    $user = $params["user"];
    $pass = $params["password"];
    $dbs  = $params["database"];

    $host = (isset($params["port"])) ? $host . " port=" . $params["port"] : $host;
    $conn = pg_connect("host={$host} dbname={$dbs} user={$user} password={$pass}");

    if ($conn) {
      if (isset($params["encoding"])) {
        pg_query($conn, sprintf(self::SET_ENCODING, $params["encoding"]));
      }

      return array($conn, "");
    } else {
      $error = error_get_last();
      return array($conn, $error["message"]);
    }
  }

  private static function ibaseConnect($params)
  {
    $host = $params["host"]. ":" . $params["database"];
    $enc  = (isset($params["encoding"])) ? $params["encoding"] : null;
    $conn = ibase_connect($host, $params["user"], $params["password"], $enc);

    if ($conn) {
      return array($conn, "");
    } else {
      return array($conn, ibase_errmsg());
    }
  }

  private static function ociConnect($params)
  {
    $database = "//" . $params["host"] . "/" . $params["database"];
    $encoding = (isset($params["encoding"])) ? $params["encoding"] : null;

    $conn = oci_connect($params["user"], $params["password"], $database, $encoding);

    if ($conn) {
      $stmt = oci_parse($conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
      oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
      return array($conn, "");
    } else {
      $e = oci_error();
      return array($conn, $e["message"]);
    }
  }

  private static function mssqlConnect($params)
  {
    $host = $params["host"];

    $host = (isset($params["port"])) ? $host . "," . $params["port"] : $host;
    $conn = mssql_connect($host, $params["user"], $params["password"]);

    if ($conn) {
      mssql_select_db($params["database"], $conn);
      return array($conn, "");
    } else {
      return array($conn, mssql_get_last_message());
    }
  }

  public static function close($connectionName)
  {
    if (!isset(self::$connections[$connectionName])) return null;

    $conn   = self::$connections[$connectionName];
    $driver = Sabel_DB_Config::loadDriver($connectionName);
    $driver->close($conn);

    unset(self::$connections[$connectionName]);
  }

  public static function closeAll()
  {
    $configs = Sabel_DB_Config::get();
    foreach ($configs as $connectionName => $config) {
      self::close($connectionName);
    }

    self::$connections = array();
  }

  private static function error($connectionName, $message, $params)
  {
    $extra = array("CONNECTION_NAME" => $connectionName,
                   "PARAMETERS"      => $params);

    $e = new Sabel_DB_Exception_Connection();
    throw $e->exception($message, $extra);
  }
}
