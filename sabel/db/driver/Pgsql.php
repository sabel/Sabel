<?php

/**
 * db driver for Pgsql
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Pgsql implements Sabel_DB_Driver_Interface
{
  private $conn, $queryObj, $myDb;
  private $lastinsertId = null;

  public function __construct($conn)
  {
    $this->conn     = $conn;
    $this->myDb     = 'pgsql';
    $this->queryObj = new Sabel_DB_Query_Normal($this);
  }

  public function getConnection()
  {
    return $this->conn;
  }

  public function begin($conn)
  {
    pg_query($conn, 'BEGIN');
  }

  public function commit($conn)
  {
    pg_query($conn, 'COMMIT');
  }

  public function rollback($conn)
  {
    pg_query($conn, 'ROLLBACK');
  }

  public function setBasicSQL($sql)
  {
    $this->queryObj->setBasicSQL($sql);
  }

  public function setUpdateSQL($table, $data)
  {
    $sql = array();

    foreach ($data as $key => $val) {
      $val = $this->escape($val);
      array_push($sql, "{$key}='{$val}'");
    }
    $this->queryObj->setBasicSQL("UPDATE {$table} SET " . join(',', $sql));
  }

  public function setAggregateSQL($table, $idColumn, $functions)
  {
    $sql = array("SELECT {$idColumn}");

    foreach ($functions as $key => $val)
      array_push($sql, ", {$key}({$val}) AS {$key}_{$val}");

    array_push($sql, " FROM {$table} GROUP BY {$idColumn}");
    $this->queryObj->setBasicSQL(join('', $sql));
  }

  public function executeInsert($table, $data, $defColumn)
  {
    if (!isset($data[$defColumn]))
      $data[$defColumn] = $this->getNextNumber($table, $defColumn);

    $columns = array();
    $values  = array();
    foreach ($data as $key => $val) {
      array_push($columns, $key);
      $val = $this->escape($val);
      array_push($values, "'{$val}'");
    }

    $sql = array("INSERT INTO {$table}(");
    array_push($sql, join(',', $columns));
    array_push($sql, ") VALUES(");
    array_push($sql, join(',', $values));
    array_push($sql, ');');

    $this->queryObj->setBasicSQL(join('', $sql));
    return $this->execute();
  }

  public function getLastInsertId()
  {
    return (isset($this->lastInsertId)) ? $this->lastInsertId : null;
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
    $this->queryObj->makeConditionQuery($conditions);

    if ($constraints)
      $this->queryObj->makeConstraintQuery($constraints);
  }

  public function execute($sql = null, $param = null)
  {
    if (isset($sql)) {
      $this->result = pg_query($this->conn, $sql);
    } else if (is_null($this->queryObj->getSQL())) {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $sql = $this->queryObj->getSQL();
      if (!($this->result = pg_query($this->conn, $sql))) {
        throw new Exception('pg_query execute failed: ' . $sql);
      }
    }

    $this->queryObj->unsetProparties();
    return true;
  }

  public function fetch($style = null)
  {
    if ($style === Sabel_DB_Driver_Interface::FETCH_ASSOC) {
      return pg_fetch_assoc($this->result);
    } else {
      return pg_fetch_array($this->result);
    }
  }

  public function fetchAll($style = null)
  {
    return pg_fetch_all($this->result);
  }

  public function escape($value)
  {
     return pg_escape_string($value);
  }
}
