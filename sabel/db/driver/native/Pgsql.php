<?php

/**
 * db driver for Pgsql
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Pgsql extends Sabel_DB_Driver_General
{
  public function __construct($conn)
  {
    $this->conn  = $conn;
    $this->query = new Sabel_DB_Driver_Native_Query('pgsql', 'pg_escape_string');
  }

  public function begin($conn)
  {
    pg_query($conn, 'BEGIN');
  }

  public function commit($conn)
  {
    pg_query($conn, 'COMMIT');
  }

  public function rollback($conn)
  {
    pg_query($conn, 'ROLLBACK');
  }

  protected function setIdNumber($table, $data, $defColumn)
  {
    if (!isset($data[$defColumn])) {
      $this->execute("SELECT nextval('{$table}_{$defColumn}_seq');");
      $row = $this->fetch();
      if (($this->lastInsertId =(int) $row[0]) === 0) {
        throw new Exception($table . '_{$defColumn}_seq is not found.');
      } else {
        $data[$defColumn] = $this->lastInsertId;
      }
    }
    return $data;
  }

  public function execute($sql = null, $param = null)
  {
    if (isset($sql)) {
      $this->result = pg_query($this->conn, $sql);
    } else if (is_null($this->query->getSQL())) {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $sql = $this->query->getSQL();
      if (!($this->result = pg_query($this->conn, $sql))) {
        throw new Exception('pg_query execute failed: ' . $sql);
      }
    }

    $this->query->unsetProparties();
    return true;
  }

  public function fetch($style = null)
  {
    if ($style === Sabel_DB_Driver_Const::ASSOC) {
      return pg_fetch_assoc($this->result);
    } else {
      return pg_fetch_array($this->result);
    }
  }

  public function fetchAll($style = null)
  {
    return pg_fetch_all($this->result);
  }
}
