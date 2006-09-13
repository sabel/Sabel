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

  public abstract function execute($sql = null, $param = null);

  public abstract function fetch($style = null);
  public abstract function fetchAll($style = null);


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

    return $this->execute();
  }

  protected function setIdNumber($table, $data, $defColumn)
  {
    if (!isset($data[$defColumn])) {
      $this->execute("SELECT nextval('{$table}_{$defColumn}_seq');");
      $row = $this->fetch();
      if (($this->lastInsertId =(int) $row[0]) === 0) {
        throw new Exception("{$table}_{$defColumn}_seq is not found.");
      } else {
        $data[$defColumn] = $this->lastInsertId;
      }
    }
    return $data;
  }

  public function setAggregateSQL($table, $idColumn, $functions)
  {
    $sql = array("SELECT {$idColumn}");

    foreach ($functions as $key => $val)
      array_push($sql, ", {$key}({$val}) AS {$key}_{$val}");

    array_push($sql, " FROM {$table} GROUP BY {$idColumn}");
    $this->query->setBasicSQL(join('', $sql));
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
}
