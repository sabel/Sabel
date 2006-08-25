<?php

/**
 * general class for db drivers.
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_General
{
  protected
    $myDb     = null,
    $queryObj = null;

  public function getDBName()
  {
    return $this->myDb;
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

  public function makeQuery($conditions, $constraints = null)
  {
    $this->queryObj->makeConditionQuery($conditions);

    if ($constraints)
      $this->queryObj->makeConstraintQuery($constraints);
  }
  
  public function executeInsert($table, $data, $defColumn)
  {
    $data = $this->setIdNumber($table, $data, $defColumn);

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
}