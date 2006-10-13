<?php

/**
 * general class for Sabel_DB_Driver pkg.
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
abstract class Sabel_DB_Driver_General
{
  protected
    $conn     = null,
    $query    = null,
    $dbType   = '',
    $insertId = null;

  public abstract function begin($conn);
  public abstract function commit($conn);
  public abstract function rollback($conn);
  public abstract function close($conn);
  public abstract function getResultSet();

  protected abstract function driverExecute($sql = null);

  public function getStatement()
  {
    return $this->query;
  }

  public function setBasicSQL($sql)
  {
    $this->query->setBasicSQL($sql);
  }

  public function setUpdateSQL($table, $data)
  {
    $this->query->makeUpdateSQL($table, $data);
  }

  public function executeInsert($table, $data, $defColumn)
  {
    if ($defColumn && ($this->dbType === 'pgsql' || $this->dbType === 'firebird'))
      $data = $this->setIdNumber($table, $data, $defColumn);

    $sql  = $this->query->makeInsertSQL($table, $data);
    $this->query->setBasicSQL($sql);

    return $this->driverExecute();
  }

  protected function setIdNumber($table, $data, $defColumn)
  {
    if (!isset($data[$defColumn])) {
      $this->driverExecute("SELECT nextval('{$table}_{$defColumn}_seq')");
      $resultSet = $this->getResultSet();
      $row = $resultSet->fetch(Sabel_DB_Driver_ResultSet::NUM);
      if (($this->lastInsertId = (int)$row[0]) === 0) {
        throw new Exception("{$table}_{$defColumn}_seq is not found.");
      } else {
        $data[$defColumn] = $this->lastInsertId;
      }
    }
    return $data;
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $this->query->makeConditionQuery($conditions);
    if ($constraints) $this->query->makeConstraintQuery($constraints);
  }

  public function getLastInsertId()
  {
    return (isset($this->lastInsertId)) ? $this->lastInsertId : null;
  }

  public function execute($sql = null, $param = null)
  {
    if ($param) {
      foreach ($param as $key => $val) $param[$key] = $this->query->escape($val);
      $sql = vsprintf($sql, $param);
    }
    $this->driverExecute($sql);
    $this->query->unsetProperties();
  }

  public function checkTableEngine($table)
  {
    $this->driverExecute("SHOW TABLE STATUS WHERE Name='{$table}'", null);
    $resultSet = $this->getResultSet();
    $res = $resultSet->fetch();
    if ($res['Engine'] !== 'InnoDB' && $res['Engine'] !== 'BDB') {
      $msg = "The Engine of '{$table}' is {$res['Engine']} though the transaction was tried.";
      trigger_error($msg, E_USER_NOTICE);
      return false;
    } else {
      return true;
    }
  }
}
