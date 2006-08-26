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
    $trans        = null,
    $lastinsertId = null;

  public function __construct($conn)
  {
    $this->conn     = $conn;
    $this->myDb     = 'firebird';
    $this->queryObj = new Sabel_DB_Query_Normal($this);
  }

  public function begin($conn)
  {
    $resource    = ibase_trans(IBASE_WRITE, $conn);
    $this->trans = $resource;
    return $resource;
  }

  public function commit($conn)
  {
    ibase_commit($conn);
    unset($this->trans);
  }

  public function rollback($conn)
  {
    ibase_rollback($conn);
    unset($this->trans);
  }

  protected function setIdNumber($table, $data, $defColumn)
  {
    $seqName = strtoupper("{$table}_{$defColumn}_gen");

    if (!isset($data[$defColumn])) {
      if (!$result = $this->execute("SELECT GEN_ID({$seqName}, 1) FROM sequence"))
        throw new Exception('Error: get generator number failed. ' . $seqName);

      $genNum = $this->fetch();
      $this->lastInsertId = $genNum[0];
      $data[$defColumn] = $genNum[0];
    }
    return $data;
  }

  public function execute($sql = null, $param = null)
  {
    $conn = (isset($this->trans)) ? $this->trans : $this->conn;

    if (isset($sql)) {
      $this->result = ibase_query($conn, $sql);
    } else if (is_null($this->queryObj->getSQL())) {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $sql = $this->queryObj->getSQL();
      if (!($this->result = @ibase_query($conn, $sql))) {
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

    if (is_array($row)) $row = array_change_key_case($row);
    return $row;
  }

  public function fetchAll($style = null)
  {
    $rows   = array();
    $result = $this->result;

    if (!is_bool($result) && !is_numeric($result) && !is_string($result))
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