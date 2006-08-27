<?php

/**
 * db driver for Mysql
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Mysql extends Sabel_DB_Driver_General
                            implements Sabel_DB_Driver_Interface
{
  private $conn = null;

  public function __construct($conn)
  {
    $this->conn     = $conn;
    $this->myDb     = 'mysql';
    $this->queryObj = new Sabel_DB_Query_Normal($this);
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

  protected function setIdNumber($table, $data, $defColumn)
  {
    return $data;
  }

  public function getLastInsertId()
  {
    $this->execute('SELECT last_insert_id()');
    $row = $this->fetch(Sabel_DB_Driver_Interface::FETCH_ASSOC);
    return $row['last_insert_id()'];
  }

  public function execute($sql = null, $param = null)
  {
    if (isset($sql)) {
      $this->result = mysql_query($sql, $this->conn);
    } else if (is_null($this->queryObj->getSQL())) {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $sql = $this->queryObj->getSQL();
      if (!($this->result = mysql_query($sql, $this->conn))) {
        throw new Exception('mysql_query execute failed: ' . $sql);
      }
    }

    $this->queryObj->unsetProparties();
    return true;
  }

  public function fetch($style = null)
  {
    if ($style === Sabel_DB_Driver_Interface::FETCH_ASSOC) {
      return mysql_fetch_assoc($this->result);
    } else {
      return mysql_fetch_array($this->result);
    }
  }

  public function fetchAll($style = null)
  {
    $rows   = array();
    $result = $this->result;

    if ($result !== true)
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;

    return $rows;
  }

  public function escape($value)
  {
    if (get_magic_quotes_gpc()) $value = stripslashes($value);
    return mysql_real_escape_string($value, $this->conn);
  }
}