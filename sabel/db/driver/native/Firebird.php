<?php

/**
 * db driver for Firebird
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Firebird extends Sabel_DB_Driver_General
{
  private $trans = null;

  public function __construct($conn)
  {
    $this->conn  = $conn;
    $this->query = new Sabel_DB_Driver_Native_Query('firebird');
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

  public function execute($sql = null, $param = null)
  {
    $conn = (isset($this->trans)) ? $this->trans : $this->conn;

    if (isset($sql)) {
      $this->result = ibase_query($conn, $sql);
    } else if (is_null($this->query->getSQL())) {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $sql = $this->query->getSQL();
      if (!($this->result = @ibase_query($conn, $sql))) {
        throw new Exception('ibase_query execute failed: ' . $sql);
      }
    }

    $this->query->unsetProparties();
    return true;
  }

  public function fetch($style = null)
  {
    if ($style === Sabel_DB_Driver_Const::ASSOC) {
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
}
