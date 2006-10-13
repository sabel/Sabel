<?php

/**
 * db driver for SQL-Server
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Native_Mssql extends Sabel_DB_Driver_General
{
  private $defCol = '';

  public function __construct($conn)
  {
    $this->conn      = $conn;
    $this->dbType    = 'mssql';
    $this->query     = new Sabel_DB_Driver_Native_Query('mssql', 'mssql_escape_string');
  }

  public function setDefaultOrderKey($pKey)
  {
    $this->defCol = $pKey;
  }

  public function begin($conn)
  {
    $this->driverExecute('BEGIN TRANSACTION', $conn);
  }

  public function commit($conn)
  {
    $this->driverExecute('COMMIT TRANSACTION', $conn);
  }

  public function rollback($conn)
  {
    $this->driverExecute('ROLLBACK TRANSACTION', $conn);
  }

  public function close($conn)
  {
    mssql_close($conn);
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $this->query->makeConditionQuery($conditions);
    if ($constraints) {
      $constraints['defCol'] = $this->defCol;
      $this->query->makeConstraintQuery($constraints);
    }
  }

  public function getLastInsertId()
  {
    $this->driverExecute('SELECT SCOPE_IDENTITY()');
    $result = $this->fetch();
    return (int)$result[0];
  }

  public function driverExecute($sql = null, $conn = null)
  {
    $conn = (is_null($conn)) ? $this->conn : $conn;

    if (isset($sql)) {
      $this->result = mssql_query($sql, $conn);
    } elseif (($sql = $this->query->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $this->result = mssql_query($sql, $conn);
    }

    if (!$this->result) {
      $error = mssql_get_last_message();
      throw new Exception("mssql_query execute failed:{$sql} ERROR:{$error}");
    }
  }

  public function getResultSet()
  {
    $rows   = array();
    $result = $this->result;

    if (is_resource($result)) {
      while ($row = mssql_fetch_assoc($result)) $rows[] = $row;
    }
    return $rows;
  }
}

function mssql_escape_string($val)
{
  return str_replace("'", "''", $val);
}