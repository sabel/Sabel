<?php

/**
 * db driver for Firebird
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Native_Firebird extends Sabel_DB_Driver_General
{
  private $trans = null;

  public function __construct($conn)
  {
    $this->conn   = $conn;
    $this->dbType = 'firebird';
    $this->query  = new Sabel_DB_Driver_Native_Query('firebird');
  }

  public function begin($conn)
  {
    $resource = ibase_trans(IBASE_WRITE, $conn);
    $this->trans = $resource;
    return $resource;
  }

  public function commit($conn)
  {
    if (!ibase_commit($conn)) {
      $error = ibase_errmsg();
      throw new Exception ("Error: transaction commit failed. {$error}");
    }
    unset($this->trans);
  }

  public function rollback($conn)
  {
    ibase_rollback($conn);
    unset($this->trans);
  }

  protected function setIdNumber($table, $data, $defColumn)
  {
    $genName = strtoupper("{$table}_{$defColumn}_gen");

    if (!isset($data[$defColumn])) {
      if (!$result = $this->execute("SELECT GEN_ID({$genName}, 1) FROM sequence"))
        throw new Exception('Error: get generator number failed. ' . $genName);

      $genNum = $this->fetch();
      $this->lastInsertId = $genNum[0];
      $data[$defColumn]   = $genNum[0];
    }
    return $data;
  }

  public function driverExecute($sql = null)
  {
    $conn = (isset($this->trans)) ? $this->trans : $this->conn;

    if (isset($sql)) {
      $this->result = ibase_query($conn, $sql);
    } elseif (($sql = $this->query->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $this->result = ibase_query($conn, $sql);
    }

    if (!$this->result) {
      $error = ibase_errmsg();
      throw new Exception("ibase_query execute failed:{$sql} ERROR:{$error}");
    }
  }

  public function fetch($style = null)
  {
    if ($style === Sabel_DB_Const::ASSOC) {
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

    if (is_resource($result))
      while ($row = ibase_fetch_assoc($result)) $rows[] = array_change_key_case($row);

    return $rows;
  }
}
