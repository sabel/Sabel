<?php

/**
 * Sabel_DB_Driver_Pdo
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Pdo extends Sabel_DB_Driver_Base
{
  protected $db = "";
  protected $bindValues = array();

  public function __construct($db)
  {
    $this->db = $db;
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

  public function getBinds()
  {
    return $this->bindValues;
  }

  public function getBeforeMethods()
  {
    if ($this->db === "pgsql") {
      return array("insert" => array("setIncrementId"));
    } else {
      return array();
    }
  }

  public function getAfterMethods()
  {
    return array("execute" => array("getResultSet"),
                 "insert"  => array("getIncrementId"));
  }

  public function getSqlClass($model, $classType = null)
  {
    return parent::getSqlClass($model, Sabel_DB_Sql_Loader::PDO);
  }

  public function getConditionBuilder($classType = null)
  {
    return parent::getConditionBuilder(Sabel_DB_Condition_Builder_Loader::PDO);
  }

  public function begin($connectionName)
  {
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
    $connection = null;
  }

  public function escape($values)
  {
    return escapeString($this->db, $values);
  }

  public function setIncrementId($command)
  {
    $this->incrementId = Sabel_DB_Driver_Sequence::getId("pgsql", $command);
  }

  public function getIncrementId($command = null)
  {
    switch ($this->db) {
      case 'pgsql':
        $id = $this->incrementId;
        break;
      case 'mysql':
        $id = Sabel_DB_Driver_Sequence::getId("mysql", $command);
        break;
      case 'sqlite':
        $id = (int)$this->connection->lastInsertId();
        break;
    }

    if ($command === null) {
      return $id;
    } else {
      $command->setIncrementId($id);
    }
  }

  public function execute($conn = null)
  {
    $sql = $this->sql;

    // @todo
    if (defined("QUERY_LOG")) {
      var_dump($sql);
    }

    if ($conn === null) $conn = $this->getPdoInstance();

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
        $param = var_export($param, 1);
        $error = $conn->errorInfo();
        $error = (isset($error[2])) ? $error[2] : var_export($error, 1);
        if (is_object($pdoStmt)) $sql = $pdoStmt->queryString;
        $sql   = substr($sql, 0, 128) . "...";
        throw new Exception("Error: pdo execute failed: $sql PARAMETERS: $param ERROR: $error");
      }
    }
  }

  protected function getPdoInstance()
  {
    if ($this->connection === null) {
      $conn = Sabel_DB_Connection::get($this->connectionName);
      $this->connection = $conn;
    } else {
      $conn = $this->connection;
    }

    return $conn;
  }

  protected function createPdoStatement($conn, $sql)
  {
    if (!($pdoStmt = $conn->prepare($sql))) {
      $this->data = array();
      $error = $conn->errorInfo();
      throw new Exception("PdoStatement is null. SQL: $sql ERROR: {$error[2]}");
    }

    return $pdoStmt;
  }

  protected function arrayExecute($conn, $sqls)
  {
    $bindValues = $this->bindValues;

    foreach ($sqls as $sql) {
      $pdoStmt = $this->createPdoStatement($conn, $sql);
      $param   = $this->createBindParam(array_shift($bindValues));
      $pdoStmt->execute($param);
    }
  }

  protected function createBindParam($bindValues = null)
  {
    $bindParam = array();
    $binds = ($bindValues === null) ? $this->bindValues : $bindValues;

    foreach ($binds as $key => $value) {
      $bindParam[":{$key}"] = $value;
    }

    $this->bindValues = array();
    return $bindParam;
  }
}
