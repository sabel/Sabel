<?php

Sabel::using('Sabel_DB_Base_Driver');
Sabel::using('Sabel_DB_General_Statement');

/**
 * Sabel_DB_Mysql_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage mysql
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysql_Driver extends Sabel_DB_Base_Driver
{
  protected
    $escMethod = 'mysql_real_escape_string';

  public function __construct($conn)
  {
    $this->conn = $conn;
    $this->db   = 'mysql';
  }

  public function begin($conName)
  {
    $trans = $this->loadTransaction();

    if (!$trans->isActive($conName)) {
      $this->driverExecute('START TRANSACTION', $this->conn);
      $trans->begin($this, $conName);
    }
  }

  public function doCommit($conn)
  {
    $this->driverExecute('COMMIT', $conn);
  }

  public function doRollback($conn)
  {
    $this->driverExecute('ROLLBACK', $conn);
  }

  public function close($conn)
  {
    mysql_close($conn);
  }

  public function getLastInsertId()
  {
    $this->driverExecute('SELECT last_insert_id()');
    $resultSet = $this->getResultSet();
    $row = $resultSet->fetch(Sabel_DB_Result_Row::NUM);
    return (int)$row[0];
  }

  public function driverExecute($sql = null, $conn = null)
  {
    $conn = ($conn === null) ? $this->conn : $conn;

    if (isset($sql)) {
      $result = mysql_query($sql, $conn);
    } elseif (($sql = $this->stmt->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $result = mysql_query($sql, $conn);
    }

    if (!$result) {
      $error = mysql_error($conn);
      throw new Exception("mysql_query execute failed:{$sql} ERROR:{$error}");
    }

    $rows = array();
    if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
    }
    $this->resultSet = new Sabel_DB_Result_Row($rows);
  }

  public function getResultSet()
  {
    return $this->resultSet;
  }
}
