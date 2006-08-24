<?php

/**
 * db driver for Mysql
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Mysql implements Sabel_DB_Driver_Interface
{
  private $conn, $queryObj, $myDb;

  public function __construct($conn)
  {
    $this->conn     = $conn;
    $this->myDb     = 'mysql';
    $this->queryObj = new Sabel_DB_Query_Normal($this);
  }

  public function getConnection()
  {
    return $this->conn;
  }

  public function begin($conn)
  {
    mysql_query('BEGIN', $conn);
  }

  public function commit($conn)
  {
    mysql_query('COMMIT', $conn);
  }

  public function rollback($conn)
  {
    mysql_query('ROLLBACK', $conn);
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
    $this->execute('SELECT last_insert_id()');
    $row = $this->fetch(Sabel_DB_Driver_Interface::FETCH_ASSOC);
    return $row['last_insert_id()'];
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
      $this->result = mysql_query($sql, $this->conn);
    } else if (is_null($this->queryObj->getSQL())) {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $sql = $this->queryObj->getSQL();
      if (!($this->result = mysql_query($sql, $this->conn))) {
        throw new Exception('mysql_query execute failed: ' . $sql);
      }
    }

    $this->queryObj->unsetProparties();
    return true;
  }

  public function fetch($style = null)
  {
    if ($style === Sabel_DB_Driver_Interface::FETCH_ASSOC) {
      return mysql_fetch_assoc($this->result);
    } else {
      return mysql_fetch_array($this->result);
    }
  }

  public function fetchAll($style = null)
  {
    $rows   = array();
    $result = $this->result;

    if ($result !== true) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
    }
    return $rows;
  }

  public function escape($value)
  {
     return mysql_real_escape_string($value, $this->conn);
  }
}
