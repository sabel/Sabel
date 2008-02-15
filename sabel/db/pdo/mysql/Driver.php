<?php

/**
 * Driver for PDO_MYSQL
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Mysql_Driver extends Sabel_DB_Pdo_Driver
{
  public function getDriverId()
  {
    return "pdomysql";
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
