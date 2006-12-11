<?php

Sabel::using('Sabel_DB_Base_Driver');
Sabel::using('Sabel_DB_General_Statement');

/**
 * Sabel_DB_Pgsql_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage pgsql
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Driver extends Sabel_DB_Base_Driver
{
  private
    $resultSet = null;

  protected
    $escMethod = 'pg_escape_string';

  public function __construct($conn)
  {
    $this->conn = $conn;
    $this->db   = 'pgsql';
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
    pg_close($conn);
  }

  public function driverExecute($sql = null, $conn = null)
  {
    $conn = ($conn === null) ? $this->conn : $conn;

    if (isset($sql)) {
      $result = pg_query($conn, $sql);
    } elseif (($sql = $this->stmt->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $result = pg_query($conn, $sql);
    }

    if (!$result) {
      $error = pg_result_error($result);
      throw new Exception("pgsql_query execute failed:{$sql} ERROR:{$error}");
    }

    $rows = (is_resource($result)) ? pg_fetch_all($result) : array();
    $this->resultSet = new Sabel_DB_Result_Row($rows);
  }

  public function getResultSet()
  {
    return $this->resultSet;
  }
}
