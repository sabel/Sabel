<?php

/**
 * Sabel_DB_Pgsql_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Driver extends Sabel_DB_Abstract_Driver
{
  public function getDriverId()
  {
    return "pgsql";
  }

  public function connect(array $params)
  {
    $host = $params["host"];
    $user = $params["user"];
    $pass = $params["password"];
    $dbs  = $params["database"];

    $host = (isset($params["port"])) ? $host . " port=" . $params["port"] : $host;
    $conn = pg_connect("host={$host} dbname={$dbs} user={$user} password={$pass}");

    if ($conn) {
      if (isset($params["charset"])) {
        pg_set_client_encoding($conn, $params["charset"]);
      }

      return $conn;
    } else {
      list (, $v) = explode(".", PHP_VERSION);

      if ($v >= 2) {
        $error = error_get_last();
        return $error["message"];
      } else {
        return "cannot connect to PostgreSQL. please check your configuration.";
      }
    }
  }

  public function begin()
  {
    $connection = $this->getConnection();
    if (pg_query($connection, "START TRANSACTION")) {
      return $connection;
    } else {
      throw new Sabel_DB_Driver_Exception("pgsql driver begin failed.");
    }
  }

  public function commit()
  {
    if (!pg_query($this->getConnection(), "COMMIT")) {
      throw new Sabel_DB_Driver_Exception("pgsql driver commit failed.");
    }
  }

  public function rollback()
  {
    if (!pg_query($this->getConnection(), "ROLLBACK")) {
      throw new Sabel_DB_Driver_Exception("pgsql driver rollback failed.");
    }
  }

  public function close($connection)
  {
    pg_close($connection);
    unset($this->connection);
  }

  public function execute($sql, $bindParams = null)
  {
    $sql = $this->bind($sql, $bindParams);
    $result = pg_query($this->getConnection(), $sql);
    if (!$result) $this->executeError($result, $sql);

    $rows = array();
    if (is_resource($result)) {
      $rows = pg_fetch_all($result);
      pg_free_result($result);
    }

    return (empty($rows)) ? null : $rows;
  }

  public function getLastInsertId()
  {
    $rows = $this->execute("SELECT LASTVAL() AS id");
    return $rows[0]["id"];
  }

  private function executeError($result, $sql)
  {
    $error = pg_result_error($result);
    $message = "pgsql driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Driver_Exception($message);
  }
}
