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

  protected static $connections = array();

  protected static function createDatabaseLink($connectionName)
  {
    $params  = Sabel_DB_Config::get($connectionName);
    $drvName = $params["driver"];

    if (strpos($drvName, "pdo-") === 0) {
      $type    = "pdo";
      $drvName = str_replace("pdo-", "", $drvName);

      if ($drvName === "sqlite") {
        $conn = new PDO("sqlite:" . $params["database"]);
      } else {
        $dsn = "{$drvName}:host={$params["host"]};dbname={$params["database"]}";
        if (isset($params["port"])) $dsn .= ";port={$params["port"]}";
        $conn = new PDO($dsn, $params["user"], $params["password"]);
      }
    } else {
      $type = "native";
      $host = $params["host"];
      $user = $params["user"];
      $pass = $params["password"];
      $dbs  = $params["database"];

      switch ($drvName) {
        case "mysql":
          $host = (isset($params["port"])) ? $host . ":" . $params["port"] : $host;
          $conn = mysql_connect($host, $user, $pass);
          mysql_select_db($dbs, $conn);
          break;
        case "pgsql":
          $host = (isset($params["port"])) ? $host . " port=" . $params["port"] : $host;
          $conn = pg_connect("host={$host} dbname={$dbs} user={$user} password={$pass}");
          break;
        case "ibase":
          $host = $host . ":" . $dbs;
          $enc  = (isset($params["encoding"])) ? $params["encoding"] : null;
          $conn = ibase_connect($host, $user, $pass, $enc);
          break;
        case "mssql":
          $host = (isset($params["port"])) ? $host . "," . $params["port"] : $host;
          $conn = mssql_connect($host, $user, $pass);
          mssql_select_db($dbs, $conn);
          break;
      }
    }

    if (!$conn) self::error($connectionName);

    if (isset($params["encoding"])) {
      $db  = $drvName;
      $enc = $params["encoding"];

      if ($type === "pdo") {
        $conn->exec(sprintf(self::SET_ENCODING, $enc));
      } elseif ($db === "mysql") {
        mysql_query(sprintf(self::SET_ENCODING, $enc), $conn);
      } elseif ($db === "pgsql") {
        pg_query($conn, sprintf(self::SET_ENCODING, $enc));
      }
    }

    self::$connections[$connectionName] = $conn;
  }

  public static function get($connectionName)
  {
    if (!isset(self::$connections[$connectionName])) {
      self::createDatabaseLink($connectionName);
    }

    if (isset(self::$connections[$connectionName])) {
      return self::$connections[$connectionName];
    } else {
      throw new Exception("database connection refused.");
    }
  }

  public static function close($connectionName)
  {
    if (!isset(self::$connections[$connectionName])) return null;

    $conn   = self::$connections[$connectionName];
    $driver = load_driver($connectionName);
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

  public static function error($connectionName)
  {
    if (class_exists('Exception_DatabaseConnection', true)) {
      Exception_DatabaseConnection::error($connectionName);
    }
  }
}
