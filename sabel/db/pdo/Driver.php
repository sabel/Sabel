<?php

/**
 * Sabel_DB_Pdo_Driver
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Pdo_Driver extends Sabel_DB_Abstract_Driver
{
  protected $placeHolderPrefix = ":";
  protected $placeHolderSuffix = "";

  public function begin()
  {
    try {
      $connection = $this->getConnection();
      $connection->beginTransaction();
    } catch (PDOException $e) {
      $message = $e->getMessage();
      throw new Sabel_DB_Exception("pdo driver begin failed. {$message}");
    }

    return $connection;
  }

  public function commit()
  {
    try {
      $this->getConnection()->commit();
    } catch (PDOException $e) {
      $message = $e->getMessage();
      throw new Sabel_DB_Exception("pdo driver commit failed. {$message}");
    }
  }

  public function rollback()
  {
    try {
      $this->getConnection()->rollback();
    } catch (PDOException $e) {
      $message = $e->getMessage();
      throw new Sabel_DB_Exception("pdo driver rollback failed. {$message}");
    }
  }

  public function close($connection)
  {
    unset($connection);
    unset($this->connection);
  }

  public function execute($sql, $bindParams = null)
  {
    $connection = $this->getConnection();
    if (!($pdoStmt = $connection->prepare($sql))) {
      $error = $connection->errorInfo();
      throw new Sabel_DB_Exception("PdoStatement is invalid. {$error[2]}");
    }

    if ($bindParams === null) {
      $bindParams = array();
    } else {
      $bindParams = $this->escape($bindParams);
    }

    if ($pdoStmt->execute($bindParams)) {
      $rows = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);
      $pdoStmt->closeCursor();
      return (empty($rows)) ? null : $rows;
    } else {
      $this->executeError($connection, $pdoStmt, $bindParams);
    }
  }

  private function executeError($conn, $pdoStmt, $bindParam)
  {
    if (is_object($pdoStmt)) {
      $error = $pdoStmt->errorInfo();
      $sql   = $pdoStmt->queryString;
    } else {
      $error = $conn->errorInfo();
    }

    $error = (isset($error[2])) ? $error[2] : print_r($error, true);
    $param = (empty($param)) ? null : $param;

    throw new Sabel_DB_Exception("pdo driver execute failed: $error");
  }
}
