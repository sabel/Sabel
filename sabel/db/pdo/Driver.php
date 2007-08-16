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
  public function loadTransaction()
  {
    return Sabel_DB_Transaction_General::getInstance();
  }

  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    }

    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      if (!$connection->beginTransaction()) {
        $error = $connection->errorInfo();
        throw new Exception("pdo driver begin failed. {$error[2]}");
      }
      $trans->start($connection, $this);
    }
  }

  public function commit($connection)
  {
    if (!$connection->commit()) {
      $error = $connection->errorInfo();
      throw new Exception("pdo driver commit failed. {$error[2]}");
    }
  }

  public function rollback($connection)
  {
    if (!$connection->rollback()) {
      $error = $connection->errorInfo();
      throw new Exception("pdo driver rollback failed. {$error[2]}");
    }
  }

  public function close($connection)
  {
    unset($connection);
    unset($this->connection);
  }

  public function execute(Sabel_DB_Abstract_Statement $stmt)
  {
    $conn = $this->getConnection();

    if (!($pdoStmt = $conn->prepare($stmt->getSql()))) {
      $error = $conn->errorInfo();
      throw new Sabel_DB_Exception("PdoStatement is invalid. {$error[2]}");
    }

    if (($bindParams = $stmt->getBindParams()) === null) {
      $bindParams = array();
    } else {
      $bindParams = $this->escape($bindParams);
    }

    if ($pdoStmt->execute($bindParams)) {
      $rows = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);
      $pdoStmt->closeCursor();
      return (empty($rows)) ? null : $rows;
    } else {
      $this->executeError($conn, $pdoStmt, $bindParams);
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
