<?php

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
      $this->result = mysql_query($sql, $conn);
    } elseif (($sql = $this->stmt->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $this->result = mysql_query($sql, $conn);
    }

    if (!$this->result) {
      $error = mysql_error($conn);
      throw new Exception("mysql_query execute failed:{$sql} ERROR:{$error}");
    }
  }

  public function getResultSet()
  {
    $rows   = array();
    $result = $this->result;

    if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
    }
    return new Sabel_DB_Result_Row($rows);
  }
}
