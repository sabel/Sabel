<?php

/**
 * db driver for Firebird
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Firebird extends Sabel_DB_Driver_General
                               implements Sabel_DB_Driver_Interface
{
  private
    $conn         = null,
    $lastinsertId = null;

  public function __construct($conn)
  {
    $this->conn     = $conn;
    $this->myDb     = 'firebird';
    $this->queryObj = new Sabel_DB_Query_Normal($this);

    ibase_query($this->conn, 'COMMIT');
  }

  public function begin($conn)
  {
    ibase_query($conn, 'SET TRANSACTION');
  }

  public function commit($conn)
  {
    pg_query($conn, 'COMMIT');
  }

  public function rollback($conn)
  {
    pg_query($conn, 'ROLLBACK');
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
    if (!($this->lastInsertId = ibase_gen_id("{$table}_{$defColumn}_seq", 1))) {
      throw new Exception("{$table}_{$defColumn}_seq is not found.");
    } else {
      return $this->lastInsertId;
    }
  }

  public function execute($sql = null, $param = null)
  {
    if (isset($sql)) {
      $this->result = ibase_query($this->conn, $sql);
    } else if (is_null($this->queryObj->getSQL())) {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $sql = $this->queryObj->getSQL();
      if (!($this->result = ibase_query($this->conn, $sql))) {
        throw new Exception('ibase_query execute failed: ' . $sql);
      }
    }

    $this->queryObj->unsetProparties();
    return true;
  }

  public function fetch($style = null)
  {
    if ($style === Sabel_DB_Driver_Interface::FETCH_ASSOC) {
      return ibase_fetch_assoc($this->result);
    } else {
      return ibase_fetch_row($this->result);
    }
  }

  public function fetchAll($style = null)
  {
    $rows   = array();
    $result = $this->result;

    while ($row = ibase_fetch_assoc($result)) $row[] = $row;
    return $rows;
  }

  public function escape($value)
  {
     if (!get_magic_quotes_gpc()) $value = addslashes($value);
     return $value;
  }
  
  public function getFirstSkipQuery($constraints, $sql)
  {
    $tmp    = substr($sql, 6);
    $query  = "FIRST {$constraints['limit']} ";
    $query .= (isset($constraints['offset']) ? "SKIP {$constraints['offset']}" : 'SKIP 0';

    return 'SELECT ' . $query . $tmp;
  }
}
