<?php

/**
 * Sabel_DB_Pdo_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Driver extends Sabel_DB_Abstract_Driver
{
  private $database = "";

  public function __construct($database)
  {
    $this->database = $database;
  }

  public function loadConstraintSqlClass()
  {
    return Sabel_DB_Sql_Constraint_Loader::load("Sabel_DB_Sql_Constraint_General");
  }

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
      $connection->beginTransaction();
      $trans->start($connection, $this);
    }
  }

  public function commit($connection)
  {
    if (!$connection->commit()) {
      $error = $connection->errorInfo();
      throw new Exception("transaction commit failed. {$error[2]}");
    }
  }

  public function rollback($connection)
  {
    $connection->rollBack();
  }

  public function close($connection)
  {
    unset($connection);
    unset($this->connection);
  }

  public function escape($values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        switch ($this->database) {
          case "mysql":
            $val = ($val) ? 1 : 0;
            break;

          case "pgsql":
            $val = ($val) ? "t" : "f";
            break;

          case "sqlite":
            $val = ($val) ? "true" : "false";
            break;
        }
      }
    }

    return $values;
  }

  public function execute($sql, $bindParam = null)
  {
    $conn = $this->getConnection();

    if (!($pdoStmt = $conn->prepare($sql))) {
      $error = $conn->errorInfo();
      throw new Sabel_DB_Exception("PdoStatement is invalid. {$error[2]}");
    }

    if ($bindParam === null) {
      $bindParam = array();
    } else {
      $bindParam = $this->escape($bindParam);
    }

    if ($pdoStmt->execute($bindParam)) {
      $rows = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);
      $pdoStmt->closeCursor();
      return $rows;
    } else {
      $this->executeError($conn, $pdoStmt, $bindParam);
    }
  }

  public function getLastInsertId(Sabel_DB_Model $model)
  {
    if ($this->database === "pgsql") {
      if (($column = $model->getIncrementColumn()) !== null) {
        $tblName = $model->getTableName();
        return $this->connection->lastInsertId("{$tblName}_{$column}_seq");
      }
    } else {
      return $this->connection->lastInsertId();
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
