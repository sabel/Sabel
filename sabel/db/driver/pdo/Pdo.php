<?php

/**
 * db driver for PDO
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Pdo extends Sabel_DB_Driver_General
{
  private
    $stmt  = null,
    $myDb  = '',
    $data  = array(),
    $param = array(),
    $conditions = array();

  public function __construct($conn, $myDb)
  {
    $this->conn  = $conn;
    $this->myDb  = $myDb;
    $this->query = new Sabel_DB_Driver_Pdo_Query($myDb);
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
    $this->data = $data;
    $sql = $this->query->makeUpdateSQL($table, $data);
    $this->query->setBasicSQL($sql);
  }

  public function executeInsert($table, $data, $defColumn)
  {
    if (!isset($data[$defColumn]) && $this->myDb === 'pgsql')
      $data[$defColumn] = $this->getNextNumber($table, $defColumn);

    $this->data = $data;
    $sql = $this->query->makeInsertSQL($table, $data);

    $this->stmtFlag = Sabel_DB_Driver_PdoStatement::exists($sql, $data);
    if (!$this->stmtFlag) $this->query->setBasicSQL($sql);

    return $this->execute();
  }

  public function getLastInsertId()
  {
    switch ($this->myDb) {
      case 'pgsql':
        return (isset($this->lastInsertId)) ? $this->lastInsertId : null;
      case 'mysql':
        $this->execute('SELECT last_insert_id()');
        $row = $this->fetch(Sabel_DB_Driver_Const::ASSOC);
        return $row['last_insert_id()'];
      case 'sqlite':
        return $this->conn->lastInsertId();
    }
  }

  private function getNextNumber($table, $defColumn = null)
  {
    $this->execute("SELECT nextval('{$table}_{$defColumn}_seq');");
    $row = $this->fetch();
    if (($this->lastInsertId =(int) $row[0]) === 0) {
      throw new Exception($table . '_{$defColumn}_seq is not found.');
    } else {
      return $this->lastInsertId;
    }
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $sql = $this->query->getSQL();
    $exist = Sabel_DB_Driver_PdoStatement::exists($sql, $conditions, $constraints);

    $result = $this->query->makeConditionQuery($conditions);
    if (!$result) $exist = false;

    if ($constraints && !$exist)
      $this->query->makeConstraintQuery($constraints);

    $this->stmtFlag = $exist;
  }

  public function execute($sql = null, $param = null)
  {
    if (isset($sql)) {
      $this->stmt = $this->conn->prepare($sql);
    } else if ($this->stmtFlag) {
      $this->stmt = Sabel_DB_Driver_PdoStatement::get();
    } else if (is_null($this->query->getSQL())) {
      throw new Exception('Error: query not exist. execute EDO::makeQuery() beforehand');
    } else {
      $sql = $this->query->getSQL();
      if ($this->stmt = $this->conn->prepare($sql)) {
        Sabel_DB_Driver_PdoStatement::add($this->stmt);
      } else {
        $error = $this->conn->errorInfo();
        throw new Exception('PDOStatement is null. sql : ' . $sql . ": {$error[2]}");
      }
    }

    $this->makeBindParam();

    if ($this->stmt->execute($this->param)) {
      $this->param = array();
      return true;
    } else {
      $msg = var_export($this->param, 1);
      throw new Exception("Error: PDOStatement::execute(): {$msg}");
    }
  }

  public function fetch($style = null)
  {
    if ($style === Sabel_DB_Driver_Const::ASSOC) {
      $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      $result = $this->stmt->fetch(PDO::FETCH_BOTH);
    }
    $this->stmt->closeCursor();
    return $result;
  }

  public function fetchAll($style = null)
  {
    if ($style === Sabel_DB_Driver_Const::ASSOC) {
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

    if ($data)
      $param = (empty($param)) ? $data : array_merge($param, $data);

    if ($param) {
      foreach ($param as $key => $val) {
        if (is_null($val)) continue;

        $param[":{$key}"] = $val;
        unset($param[$key]);
      }
    }

    $this->param = $param;
    $this->data  = array();
    $this->query->unsetProparties();
  }
}
