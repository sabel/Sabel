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
  }

  public function begin($conn)
  {
    ibase_trans($conn);
  }

  public function commit($conn)
  {
    ibase_commit($conn);
  }

  public function rollback($conn)
  {
    ibase_rollback($conn);
  }

  private function setIdNumber($table, $data, $defColumn)
  {
    if (!isset($data[$defColumn])) {
      if (!($this->lastInsertId = ibase_gen_id("{$table}_{$defColumn}_seq", 1))) {
        throw new Exception("{$table}_{$defColumn}_seq is not found.");
      } else {
        $data[$defColumn] = return $this->lastInsertId;
      }
    }
    return $data;
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
      $row = ibase_fetch_assoc($this->result);
    } else {
      $row = ibase_fetch_row($this->result);
    }
    return array_change_key_case($row);
  }

  public function fetchAll($style = null)
  {
    $rows   = array();
    $result = $this->result;

    if ($result !== true)
      while ($row = ibase_fetch_assoc($result)) $rows[] = array_change_key_case($row);        

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
    $query .= (isset($constraints['offset'])) ? "SKIP {$constraints['offset']}" : 'SKIP 0';

    return 'SELECT ' . $query . $tmp;
  }
}
