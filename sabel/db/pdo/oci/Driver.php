<?php

/**
 * Driver for PDO_OCI
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Oci_Driver extends Sabel_DB_Pdo_Driver
{
  private $lastInsertId = null;
  
  public function getDriverId()
  {
    return "pdooci";
  }
  
  public function connect(array $params)
  {
    try {
      $dsn = "oci:dbname=//{$params["host"]}";
      if (isset($params["port"])) $dsn .= ":port={$params["port"]}";
      $dsn .= "/" . $params["database"];
      if (isset($params["charset"])) $dsn .= ";charset={$params["charset"]}";
      
      $conn = new PDO($dsn, $params["user"], $params["password"]);
      $pdoStmt = $conn->prepare("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
      $pdoStmt->execute();
      
      return $conn;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
  
  public function execute($sql, $bindParams = null)
  {
    if (($result = parent::execute($sql, $bindParams)) === null) {
      return null;
    } else {
      return array_map("array_change_key_case", $result);
    }
  }
  
  public function setLastInsertId($id)
  {
    $this->lastInsertId = $id;
  }
  
  public function getLastInsertId()
  {
    return $this->lastInsertId;
  }
}
