<?php

/**
 * db driver for Pgsql
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Native_Pgsql extends Sabel_DB_Driver_General
{
  public function __construct($conn)
  {
    $this->conn   = $conn;
    $this->dbType = 'pgsql';
    $this->query  = new Sabel_DB_Driver_Native_Query('pgsql', 'pg_escape_string');
  }

  public function begin($conn)
  {
    $this->driverExecute('BEGIN', $conn);
  }

  public function commit($conn)
  {
    $this->driverExecute('COMMIT', $conn);
  }

  public function rollback($conn)
  {
    $this->driverExecute('ROLLBACK', $conn);
  }

  public function driverExecute($sql = null, $conn = null)
  {
    $conn = (is_null($conn)) ? $this->conn : $conn;

    if (isset($sql)) {
      $this->result = pg_query($conn, $sql);
    } elseif (($sql = $this->query->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $this->result = pg_query($conn, $sql);
    }

    if (!$this->result) {
      $error = pg_result_error($this->result);
      throw new Exception("pgsql_query execute failed:{$sql} ERROR:{$error}");
    }
  }

  public function fetch($style = null)
  {
    return ($style === Sabel_DB_Const::ASSOC) ? pg_fetch_assoc($this->result) : pg_fetch_array($this->result);
  }

  public function fetchAll($style = null)
  {
    return pg_fetch_all($this->result);
  }
}
