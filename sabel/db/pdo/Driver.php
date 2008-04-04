<?php

/**
 * Abstract Driver for PDO
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Pdo_Driver extends Sabel_DB_Abstract_Driver
{
  public function begin($isolationLevel = null)
  {
    if ($isolationLevel !== null) {
      $this->setTransactionIsolationLevel($isolationLevel);
    }
    
    try {
      $this->connection->beginTransaction();
      $this->autoCommit = false;
      return $this->connection;
    } catch (PDOException $e) {
      throw new Sabel_DB_Exception_Driver($e->getMessage());
    }
  }
  
  public function commit()
  {
    try {
      $this->connection->commit();
      $this->autoCommit = true;
    } catch (PDOException $e) {
      throw new Sabel_DB_Exception_Driver($e->getMessage());
    }
  }
  
  public function rollback()
  {
    try {
      $this->connection->rollback();
      $this->autoCommit = true;
    } catch (PDOException $e) {
      throw new Sabel_DB_Exception_Driver($e->getMessage());
    }
  }
  
  public function close($connection)
  {
    unset($connection);
    unset($this->connection);
  }
  
  public function execute($sql, $bindParams = array())
  {
    $connection = $this->connection;
    if (!($pdoStmt = $connection->prepare($sql))) {
      $error = $connection->errorInfo();
      throw new Sabel_DB_Exception_Driver("PdoStatement is invalid. {$error[2]}");
    }
    
    if ($pdoStmt->execute($bindParams)) {
      $rows = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);
      $this->affectedRows = $pdoStmt->rowCount();
      $pdoStmt->closeCursor();
      return (empty($rows)) ? null : $rows;
    } else {
      $this->executeError($connection, $pdoStmt, $bindParams);
    }
  }
  
  private function executeError($conn, $pdoStmt, $bindParams)
  {
    if (is_object($pdoStmt)) {
      $error = $pdoStmt->errorInfo();
      $sql   = $pdoStmt->queryString;
    } else {
      $error = $conn->errorInfo();
      $sql   = null;
    }
    
    $error = (isset($error[2])) ? $error[2] : print_r($error, true);
    if ($sql !== null) $error .= ", SQL: $sql";
    
    if (!empty($bindParams)) {
      $error .= PHP_EOL . "BIND_PARAMS: " . print_r($bindParams, true);
    }
    
    throw new Sabel_DB_Exception_Driver($error);
  }
}
