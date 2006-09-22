<?php

/**
 * db driver for Mysql
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Native_Mysql extends Sabel_DB_Driver_General
{
  public function __construct($conn)
  {
    $this->conn   = $conn;
    $this->dbType = 'mysql';
    $this->query  = new Sabel_DB_Driver_Native_Query('mysql', 'mysql_real_escape_string');
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

  public function getLastInsertId()
  {
    $this->execute('SELECT last_insert_id()');
    $row = $this->fetch(Sabel_DB_Const::ASSOC);
    return (int)$row['last_insert_id()'];
  }

  public function driverExecute($sql = null)
  {
    if (isset($sql)) {
      $this->result = mysql_query($sql, $this->conn);
    } elseif (($sql = $this->query->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $this->result = mysql_query($sql, $this->conn);
    }

    if (!$this->result) {
      $error = mysql_error($this->conn);
      throw new Exception("mysql_query execute failed:{$sql} ERROR:{$error}");
    }
  }

  public function fetch($style = null)
  {
    if ($style === Sabel_DB_Const::ASSOC) {
      return mysql_fetch_assoc($this->result);
    } else {
      return mysql_fetch_array($this->result);
    }
  }

  public function fetchAll($style = null)
  {
    $rows   = array();
    $result = $this->result;

    if (!is_bool($result))
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;

    return $rows;
  }
}
