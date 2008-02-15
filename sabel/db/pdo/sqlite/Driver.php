<?php

/**
 * Driver for PDO_SQLITE
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Sqlite_Driver extends Sabel_DB_Pdo_Driver
{
  public function getDriverId()
  {
    return "pdosqlite";
  }
  
  public function connect(array $params)
  {
    try {
      return new PDO("sqlite:" . $params["database"]);
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
  
  public function begin($isolationLevel = null)
  {
    try {
      $this->connection->beginTransaction();
      return $this->connection;
    } catch (PDOException $e) {
      $message = $e->getMessage();
      throw new Sabel_DB_Exception_Driver("pdo driver begin failed. {$message}");
    }
  }
  
  public function getLastInsertId()
  {
    return $this->connection->lastInsertId();
  }
  
  public function setTransactionIsolationLevel($level)
  {
    
  }
}
