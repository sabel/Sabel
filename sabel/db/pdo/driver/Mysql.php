<?php

/**
 * Sabel_DB_Pdo_Driver_Mysql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Driver_Mysql extends Sabel_DB_Pdo_Driver
{
  public function getDriverId()
  {
    return "pdo-mysql";
  }

  public function connect(array $params)
  {
    try {
      $dsn = "mysql:host={$params["host"]};dbname={$params["database"]}";
      if (isset($params["port"])) $dsn .= ";port={$params["port"]}";
      $conn = new PDO($dsn, $params["user"], $params["password"]);

      if (isset($params["charset"])) {
        $conn->exec("SET NAMES " . $params["charset"]);
      }

      return $conn;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getLastInsertId()
  {
    return $this->connection->lastInsertId();
  }
}
