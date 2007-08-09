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
  private $database   = "";
  private $bindValues = array();

  public function __construct($database)
  {
    $this->database = $database;
  }

  public function loadSqlClass($model)
  {
    return Sabel_DB_Sql_Loader::load($model, "Sabel_DB_Sql_Pdo");
  }

  public function loadConditionBuilder()
  {
    return Sabel_DB_Condition_Builder_Loader::load($this, "Sabel_DB_Condition_Builder_Pdo");
  }

  public function loadConstraintSqlClass()
  {
    return Sabel_DB_Sql_Constraint_Loader::load("Sabel_DB_Sql_Constraint_General");
  }

  public function setBindValues($bindValues, $add = true)
  {
    if ($add) {
      foreach ($bindValues as $key => $val) {
        $this->bindValues[$key] = $val;
      }
    } else {
      $this->bindValues = $bindValues;
    }
  }

  public function getAfterMethods()
  {
    return array(Sabel_DB_Command::INSERT => "getIncrementId");
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
    return escapeString($this->database, $values);
  }

  public function getIncrementId($command)
  {
    if ($this->database === "pgsql") {
      $model = $command->getModel();
      if ($column = $model->getIncrementColumn()) {
        $tblName = $model->getTableName();
        $id = (int)$this->connection->lastInsertId("{$tblName}_{$column}_seq");
      } else {
        $id = null;
      }
    } else {
      $id = (int)$this->connection->lastInsertId();
    }

    $command->setIncrementId($id);
  }

  public function execute()
  {
    $sql = $this->sql;

    // @todo
    if (defined("QUERY_LOG") && ENVIRONMENT === DEVELOPMENT) {
      var_dump($sql);
    }

    $conn = $this->getConnection();

    if (is_array($sql)) {
      $this->arrayExecute($conn, $sql);
    } else {
      $pdoStmt = $this->createPdoStatement($conn, $sql);
      $param   = $this->createBindParam();

      if ($pdoStmt->execute($param)) {
        $this->result = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);
        $pdoStmt->closeCursor();
        return $this->result;
      } else {
        $this->executeError($conn, $pdoStmt, $param);
      }
    }
  }

  private function createPdoStatement($conn, $sql)
  {
    if (!($pdoStmt = $conn->prepare($sql))) {
      $this->data = array();
      $error = $conn->errorInfo();
      $this->error("PdoStatement is invalid. {$error[2]}", $sql);
    }

    return $pdoStmt;
  }

  private function createBindParam($bindValues = null)
  {
    $bindParam = array();
    $binds = ($bindValues === null) ? $this->bindValues : $bindValues;

    foreach ($binds as $key => $value) {
      $bindParam[":{$key}"] = $value;
    }

    $this->bindValues = array();
    return $bindParam;
  }

  private function arrayExecute($conn, $sqls)
  {
    $bindValues = $this->bindValues;

    foreach ($sqls as $sql) {
      $pdoStmt = $this->createPdoStatement($conn, $sql);
      $param   = $this->createBindParam(array_shift($bindValues));

      if (!$pdoStmt->execute($param)) {
        $this->executeError($conn, $pdoStmt, $param);
        break;
      }
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

    $this->error("pdo driver execute failed: $error", $sql, $param);
  }

  protected function error($error, $sql = null, $pdoBind = null)
  {
    if ($pdoBind) {
      $extra = array("PDO_BIND_VALUES" => $pdoBind);
    } else {
      $extra = null;
    }

    $e = new Sabel_DB_Exception_Driver();
    throw $e->exception($this->sql, $error, $this->connectionName, $extra);
  }
}

