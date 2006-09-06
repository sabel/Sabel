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
    $queryObj = null,
    $insertId = null;

  public abstract function begin($conn);
  public abstract function commit($conn);
  public abstract function rollback($conn);

  public abstract function execute($sql = null, $param = null);

  public abstract function fetch($style = null);
  public abstract function fetchAll($style = null);


  public function setBasicSQL($sql)
  {
    $this->queryObj->setBasicSQL($sql);
  }

  public function setUpdateSQL($table, $data)
  {
    $this->queryObj->setUpdateSQL($table, $data);
  }

  public function executeInsert($table, $data, $defColumn)
  {
    $data = $this->setIdNumber($table, $data, $defColumn);
    $this->queryObj->setInsertSQL($table, $data);

    return $this->execute();
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

  public function getLastInsertId()
  {
    return (isset($this->lastInsertId)) ? $this->lastInsertId : null;
  }
}
