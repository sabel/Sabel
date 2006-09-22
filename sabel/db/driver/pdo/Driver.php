<?php

/**
 * db driver for PDO
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Pdo_Driver extends Sabel_DB_Driver_General
{
  private
    $stmt  = null,
    $data  = array(),
    $param = array();

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
    $conn->commit();
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

    $results    = $this->query->makeInsertSQL($table, $data);
    $sql        = $results[0];
    $this->data = $results[1];

    $this->stmtFlag = Sabel_DB_Driver_Pdo_Statement::exists($sql, $this->data);
    if (!$this->stmtFlag) $this->query->setBasicSQL($sql);

    return $this->execute();
  }

  public function getLastInsertId()
  {
    switch ($this->dbType) {
      case 'pgsql':
        return (isset($this->lastInsertId)) ? $this->lastInsertId : null;
      case 'mysql':
        $this->execute('SELECT last_insert_id()');
        $row = $this->fetch(Sabel_DB_Const::ASSOC);
        return $row['last_insert_id()'];
      case 'sqlite':
        return $this->conn->lastInsertId();
    }
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $sql = $this->query->getSQL();
    $exist = Sabel_DB_Driver_Pdo_Statement::exists($sql, $conditions, $constraints);

    $result = $this->query->makeConditionQuery($conditions);
    if (!$result) $exist = false;

    if ($constraints && !$exist) $this->query->makeConstraintQuery($constraints);
    $this->stmtFlag = $exist;
  }

  public function driverExecute($sql = null)
  {
    $getSQL = $this->query->getSQL();

    if (isset($sql)) {
      $this->stmt = $this->conn->prepare($sql);
    } else if ($this->stmtFlag) {
      $this->stmt = Sabel_DB_Driver_Pdo_Statement::get();
    } else if (is_null($getSQL)) {
      throw new Exception('Error: query not exist. execute EDO::makeQuery() beforehand');
    } else {
      $sql = $getSQL;
      if ($this->stmt = $this->conn->prepare($sql)) Sabel_DB_Driver_Pdo_Statement::add($this->stmt);
    }

    if (!$this->stmt) {
      $error = $this->conn->errorInfo();
      throw new Exception('PDOStatement is null. sql : ' . $sql . ": {$error[2]}");
    }

    $this->makeBindParam();

    if ($this->stmt->execute($this->param)) {
      $this->param = array();
    } else {
      $param = var_export($this->param, 1);
      $error = $this->conn->errorInfo();
      throw new Exception("pdo execute failed:{$sql} PARAMETERS:{$param} ERROR:{$error[2]}");
    }
  }

  public function fetch($style = null)
  {
    if ($style === Sabel_DB_Const::ASSOC) {
      $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      $result = $this->stmt->fetch(PDO::FETCH_BOTH);
    }
    $this->stmt->closeCursor();
    return $result;
  }

  public function fetchAll($style = null)
  {
    if ($style === Sabel_DB_Const::ASSOC) {
      $result = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      $result = $this->stmt->fetchAll(PDO::FETCH_BOTH);
    }
    $this->stmt->closeCursor();
    return $result;
  }

  private function makeBindParam()
  {
    $param = $this->query->getParam();
    $data  = $this->data;

    if ($data) $param = (empty($param)) ? $data : array_merge($param, $data);

    $bindParam = array();
    if ($param) {
      foreach ($param as $key => $val) {
        if (is_null($val)) continue;
        $bindParam[":{$key}"] = $val;
      }
    }

    $this->param = $bindParam;
    $this->data  = array();
  }
}
