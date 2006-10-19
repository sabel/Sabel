<?php

/**
 * db driver for PDO
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Pdo_Driver extends Sabel_DB_Driver_General
{
  private $stmt     = null;
  private $data     = array();
  private $stmtFlag = false;

  public function __construct($conn, $dbType)
  {
    $this->conn   = $conn;
    $this->dbType = $dbType;
    $this->query  = new Sabel_DB_Driver_Pdo_Query($dbType);
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

  public function setUpdateSQL($table, $data)
  {
    $this->data = $this->query->makeUpdateSQL($table, $data);
  }

  public function executeInsert($table, $data, $defColumn)
  {
    if ($defColumn && $this->dbType === 'pgsql')
      $data = $this->setIdNumber($table, $data, $defColumn);

    list($sql, $this->data) = $this->query->makeInsertSQL($table, $data);
    $this->stmtFlag = Sabel_DB_Driver_Pdo_Statement::exists($sql, $this->data);
    if (!$this->stmtFlag) $this->query->setBasicSQL($sql);

    return $this->driverExecute();
  }

  public function getLastInsertId()
  {
    switch ($this->dbType) {
      case 'pgsql':
        return (isset($this->lastInsertId)) ? $this->lastInsertId : null;
      case 'mysql':
        $this->driverExecute('SELECT last_insert_id()');
        $resultSet = $this->getResultSet();
        $row = $resultSet->fetch(Sabel_DB_Driver_ResultSet::NUM);
        return (int)$row[0];
      case 'sqlite':
        return (int)$this->conn->lastInsertId();
    }
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $sql = $this->query->getSQL();

    $exist = false;
    if ($this->checkConditionTypes($conditions))
      $exist = Sabel_DB_Driver_Pdo_Statement::exists($sql, $conditions, $constraints);

    $this->query->makeConditionQuery($conditions);
    if ($constraints && !$exist) $this->query->makeConstraintQuery($constraints);
    $this->stmtFlag = $exist;
  }

  private function checkConditionTypes($conditions)
  {
    if (empty($conditions)) return true;

    foreach ($conditions as $condition) {
      if (is_array($condition)) return false;
      if ($condition->type !== Sabel_DB_Condition::NORMAL) return false;
    }
    return true;
  }

  public function driverExecute($sql = null)
  {
    if (isset($sql)) {
      $this->stmt = $this->conn->prepare($sql);
    } else if ($this->stmtFlag) {
      $this->stmt = Sabel_DB_Driver_Pdo_Statement::get();
    } else if (($sql = $this->query->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute EDO::makeQuery() beforehand');
    } else {
      if ($this->stmt = $this->conn->prepare($sql)) Sabel_DB_Driver_Pdo_Statement::add($this->stmt);
    }

    if (!$this->stmt) {
      $this->data = array();
      $error = $this->conn->errorInfo();
      throw new Exception('PDOStatement is null. sql : ' . $sql . ": {$error[2]}");
    }

    $param = $this->makeBindParam();
    if (!$this->stmt->execute($param)) {
      $param = var_export($param, 1);
      $error = $this->conn->errorInfo();
      throw new Exception("pdo execute failed:{$sql} PARAMETERS:{$param} ERROR:{$error[2]}");
    }
  }

  public function getResultSet()
  {
    $result = $this->stmt->fetchAll(PDO::FETCH_ASSOC);

    $this->stmt->closeCursor();
    return new Sabel_DB_Driver_ResultSet($result);
  }

  private function makeBindParam()
  {
    $param = $this->query->getParam();
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
