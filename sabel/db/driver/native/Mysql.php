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

  public function getLastInsertId()
  {
    $this->driverExecute('SELECT last_insert_id()');
    $resultSet = $this->fetch();
    $row = $resultSet->fetch();
    return (int)$row[0];
  }

  public function driverExecute($sql = null, $conn = null)
  {
    $conn = (is_null($conn)) ? $this->conn : $conn;

    if (isset($sql)) {
      $this->result = mysql_query($sql, $conn);
    } elseif (($sql = $this->query->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $this->result = mysql_query($sql, $conn);
    }

    if (!$this->result) {
      $error = mysql_error($conn);
      throw new Exception("mysql_query execute failed:{$sql} ERROR:{$error}");
    }
  }

  public function fetch($style = null)
  {
    $result = ($style === Sabel_DB_Mapper::ASSOC) ? mysql_fetch_assoc($this->result)
                                                  : mysql_fetch_array($this->result);

    $resultSet = new Sabel_DB_ResultSet();
    $resultSet->add($result);
    return $resultSet;
  }

  public function fetchAll($style = null)
  {
    $rows   = array();
    $result = $this->result;

    if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
    }
    return new Sabel_DB_ResultSet($rows);
  }
}
