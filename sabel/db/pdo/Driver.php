<?php

Sabel::using('Sabel_DB_Base_Driver');
Sabel::using('Sabel_DB_Pdo_Statement');
Sabel::using('Sabel_DB_Pdo_PdoStatement');

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
    $conn     = null,
    $db       = '',
    $data     = array(),
    $isAdd    = true,
    $stmtFlag = false;

  public function __construct($db)
  {
    $this->db   = $db;
    $this->stmt = new Sabel_DB_Pdo_Statement($db);
  }

  public function loadStatement()
  {
    return $this->stmt;
  }

  public function begin($conName)
  {
    $trans = $this->loadTransaction();

    if (!$trans->isActive($conName)) {
      $conn = Sabel_DB_Connection::getConnection($conName);
      $conn->beginTransaction();
      $trans->begin($this, $conName);
    }
  }

  public function doCommit($conn)
  {
    if (!$conn->commit()) {
      $error = $conn->errorInfo();
      throw new Exception('Error: transaction commit failed. ' . $error[2]);
    }
  }

  public function doRollback($conn)
  {
    $conn->rollBack();
  }

  public function close($conn)
  {
    $conn = null;
  }

  public function update($table, $data, $conditions = null)
  {
    $this->makeUpdateQuery($table, $data, $conditions);
    $this->data = $this->stmt->getBindData();
    $this->execute();
  }

  public function insert($table, $data, $idColumn)
  {
    $this->makeInsertQuery($table, $data, $idColumn);

    $sql  = $this->stmt->getSQL();
    $data = $this->stmt->getBindData();
    $this->stmtFlag = Sabel_DB_Pdo_PdoStatement::exists($sql, $data);

    $this->data = $data;
    $this->execute();
  }

  public function setIdNumber($table, $data, $defColumn)
  {
    if ($this->db !== 'pgsql') return $data;

    if ($defColumn !== null && !isset($data[$defColumn])) {
      $this->driverExecute("SELECT nextval('{$table}_{$defColumn}_seq')");
      $row = $this->getResultSet()->fetch(Sabel_DB_Result_Row::NUM);
      if (($this->lastInsertId = (int)$row[0]) === 0) {
        throw new Exception("{$table}_{$defColumn}_seq is not found.");
      } else {
        $data[$defColumn] = $this->lastInsertId;
      }
    }
    return $data;
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
    $exist = false;
    if ($this->isAdd = $this->checkConditionTypes($conditions)) {
      $sql   = $this->stmt->getSQL();
      $exist = Sabel_DB_Pdo_PdoStatement::exists($sql, $conditions, $constraints);
    }

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
    $conn = Sabel_DB_Connection::getConnection($this->connectName);

    if (isset($sql)) {
      $pdoStmt = $conn->prepare($sql);
    } elseif ($this->stmtFlag) {
      $pdoStmt = Sabel_DB_Pdo_PdoStatement::get();
    } elseif (($sql = $this->stmt->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $pdoStmt = $conn->prepare($sql);
      if ($this->isAdd) Sabel_DB_Pdo_PdoStatement::add($pdoStmt);
    }

    if (!$pdoStmt) {
      $this->data = array();
      $error = $conn->errorInfo();
      throw new Exception('Error: PDOStatement is null. SQL: ' . $sql . " ERROR: {$error[2]}");
    }

    $param = $this->makeBindParam();
    if ($pdoStmt->execute($param)) {
      $rows = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);
      $pdoStmt->closeCursor();
      $this->resultSet = new Sabel_DB_Result_Row($rows);
      $this->conn = $conn;
    } else {
      $param = var_export($param, 1);
      $error = $conn->errorInfo();
      $error = (isset($error[2])) ? $error[2] : var_export($error, 1);
      if (is_object($pdoStmt)) $sql = $pdoStmt->queryString;
      $sql   = substr($sql, 0, 128) . " ...";
      throw new Exception("Error: pdo execute failed: $sql PARAMETERS: $param ERROR: $error");
    }
  }

  private function makeBindParam()
  {
    $param = ($this->stmt === null) ? array() : $this->stmt->getParam();
    $data  =& $this->data;

    if ($data) $param = (empty($param)) ? $data : array_merge($param, $data);
    $data = array();

    $bindParam = array();
    if ($param) {
      foreach ($param as $key => $val) $bindParam[":{$key}"] = $val;
    }
    return $bindParam;
  }
}
