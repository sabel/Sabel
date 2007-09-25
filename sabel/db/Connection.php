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
      $result  = self::pdoConnect($drvName, $params);
    } else {
      $currentLevel = error_reporting(0);

      switch ($drvName) {
        case "mysql":
          $result = self::mysqlConnect($params);
          break;

        case "mysqli":
          $result = self::mysqliConnect($params);
          break;

        case "pgsql":
          $result = self::pgsqlConnect($params);
          break;

        case "oci":
          $result = self::ociConnect($params);
          break;

        case "ibase":
          $result = self::ibaseConnect($params);
          break;

        case "mssql":
          $result = self::mssqlConnect($params);
          break;
      }

      error_reporting($currentLevel);
    }

    if (is_string($result)) {
      throw new Sabel_DB_Exception($result);
    } else {
      self::$connections[$connectionName] = $result;
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

        if (isset($params["encoding"])) {
          $conn->exec(sprintf(self::SET_ENCODING, $params["encoding"]));
        }
      }

      return $conn;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  private static function mysqlConnect($params)
  {
    $host = $params["host"];
    $host = (isset($params["port"])) ? $host . ":" . $params["port"] : $host;
    $conn = mysql_connect($host, $params["user"], $params["password"], true);

    if ($conn) {
      if (!mysql_select_db($params["database"], $conn)) {
        return mysql_error();
      }

      if (isset($params["encoding"])) {
        list (,,$v) = explode(".", PHP_VERSION);
        if ($v{0} >= 3) {
          mysql_set_charset($params["encoding"], $conn);
        } else {
          mysql_query(sprintf(self::SET_ENCODING, $params["encoding"]), $conn);
        }
      }

      return $conn;
    } else {
      return mysql_error();
    }
  }

  private static function mysqliConnect($params)
  {
    $h = $params["hoge"];
    $u = $params["user"];
    $p = $params["password"];
    $d = $params["database"];

    if (isset($params["port"])) {
      $conn = mysqli_connect($h, $u, $p, $d, (int)$params["port"]);
    } else {
      $conn = mysqli_connect($h, $u, $p, $d);
    }

    if ($conn) {
      if (isset($params["encoding"])) {
        mysqli_set_charset($conn, $params["encoding"]);
      }

      return $conn;
    } else {
      return mysqli_connect_error();
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
        pg_set_client_encoding($conn, $params["encoding"]);
      }

      return $conn;
    } else {
      list (, $v) = explode(".", PHP_VERSION);

      if ($v >= 2) {
        $error = error_get_last();
        return $error["message"];
      } else {
        $message = "cannot connect to PostgreSQL. please check your configuration.";
        return $message;
      }
    }
  }

  private static function ibaseConnect($params)
  {
    $host = $params["host"]. ":" . $params["database"];
    $enc  = (isset($params["encoding"])) ? $params["encoding"] : null;
    $conn = ibase_connect($host, $params["user"], $params["password"], $enc);

    if ($conn) {
      return $conn;
    } else {
      return ibase_errmsg();
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
      return $conn;
    } else {
      $e = oci_error();
      return $e["message"];
    }
  }

  private static function mssqlConnect($params)
  {
    $host = $params["host"];
    $host = (isset($params["port"])) ? $host . "," . $params["port"] : $host;
    $conn = mssql_connect($host, $params["user"], $params["password"], true);

    if ($conn) {
      mssql_select_db($params["database"], $conn);
      return $conn;
    } else {
      return mssql_get_last_message();
    }
  }

  public static function close($connectionName)
  {
    if (!isset(self::$connections[$connectionName])) return null;

    $conn   = self::$connections[$connectionName];
    $driver = Sabel_DB_Driver::create($connectionName);
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
}
