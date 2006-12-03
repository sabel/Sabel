<?php

/**
 * Sabel_DB_Pdo_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage pdo
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Driver extends Sabel_DB_Base_Driver
{
  private
    $pdoStmt  = null,
    $data     = array(),
    $isAdd    = true,
    $stmtFlag = false;

  public function __construct($conn, $db)
  {
    $this->conn = $conn;
    $this->db   = $db;
  }

  public function loadStatement()
  {
    $this->stmt = new Sabel_DB_Pdo_Statement($this->db);
    return $this->stmt;
  }

  public function begin($conn)
  {
    $conn->beginTransaction();
  }

  public function commit($conn)
  {
    if (!$conn->commit()) {
      $error = $this->conn->errorInfo();
      throw new Exception('Error: transaction commit failed. ' . $error[2]);
    }
  }

  public function close($conn)
  {
    $conn = null;
  }

  public function rollback($conn)
  {
    $conn->rollBack();
  }

  public function update()
  {
    $this->data = $this->stmt->getBindData();
    return $this->driverExecute();
  }

  public function insert()
  {
    $sql  = $this->stmt->getSQL();
    $data = $this->stmt->getBindData();
    $this->stmtFlag = Sabel_DB_Pdo_PdoStatement::exists($sql, $data);

    $this->data = $data;
    return $this->driverExecute();
  }

  public function getLastInsertId()
  {
    switch ($this->db) {
      case 'pgsql':
        return (isset($this->lastInsertId)) ? $this->lastInsertId : null;
      case 'mysql':
        $this->driverExecute('SELECT last_insert_id()');
        $resultSet = $this->getResultSet();
        $row = $resultSet->fetch(Sabel_DB_Result_Row::NUM);
        return (int)$row[0];
      case 'sqlite':
        return (int)$this->conn->lastInsertId();
    }
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $sql = $this->stmt->getSQL();

    $exist = false;
    if ($this->isAdd = $this->checkConditionTypes($conditions))
      $exist = Sabel_DB_Pdo_PdoStatement::exists($sql, $conditions, $constraints);

    $this->stmt->makeConditionQuery($conditions);
    if ($constraints && !$exist) $this->stmt->makeConstraintQuery($constraints);
    $this->stmtFlag = $exist;
  }

  private function checkConditionTypes($conditions)
  {
    if (empty($conditions)) return true;

    foreach ($conditions as $condition) {
      if (is_array($condition) || $condition->not ||
          $condition->type !== Sabel_DB_Condition::NORMAL) return false;
    }
    return true;
  }

  public function driverExecute($sql = null)
  {
    if (isset($sql)) {
      $pdoStmt = $this->conn->prepare($sql);
    } elseif ($this->stmtFlag) {
      $pdoStmt = Sabel_DB_Pdo_PdoStatement::get();
    } elseif (($sql = $this->stmt->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $pdoStmt = $this->conn->prepare($sql);
      if ($this->isAdd) Sabel_DB_Pdo_PdoStatement::add($pdoStmt);
    }

    if (!$pdoStmt) {
      $this->data = array();
      $error = $this->conn->errorInfo();
      throw new Exception('Error: PDOStatement is null. SQL: ' . $sql . " ERROR: {$error[2]}");
    }

    $param = $this->makeBindParam();
    if ($pdoStmt->execute($param)) {
      $this->pdoStmt = $pdoStmt;
    } else {
      $param = var_export($param, 1);
      $error = $this->conn->errorInfo();
      $error = (isset($error[2])) ? $error[2] : var_export($error, 1);
      if (is_object($this->pdoStmt)) $sql = $this->pdoStmt->queryString;
      throw new Exception("Error: pdo execute failed: $sql PARAMETERS: $param ERROR: $error");
    }
  }

  public function getResultSet()
  {
    $result = $this->pdoStmt->fetchAll(PDO::FETCH_ASSOC);

    $this->pdoStmt->closeCursor();
    return new Sabel_DB_Result_Row($result);
  }

  private function makeBindParam()
  {
    $param = ($this->stmt === null) ? array() : $this->stmt->getParam();
    $data  = $this->data;

    if ($data) $param = (empty($param)) ? $data : array_merge($param, $data);
    $this->data = array();
    $data = array();

    $bindParam = array();
    if ($param) {
      foreach ($param as $key => $val) $bindParam[":{$key}"] = $val;
    }
    return $bindParam;
  }
}
