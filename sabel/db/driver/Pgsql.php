<?php

/**
 * Sabel_DB_Driver_Pgsql
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage driver
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Pgsql extends Sabel_DB_Driver
{
  protected
    $escMethod = 'pg_escape_string';

  public function __construct($conn)
  {
    $this->conn = $conn;
    $this->db   = 'pgsql';
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
    pg_close($conn);
  }

  public function driverExecute($sql = null, $conn = null)
  {
    $conn = ($conn === null) ? $this->conn : $conn;

    if (isset($sql)) {
      $this->result = pg_query($conn, $sql);
    } elseif (($sql = $this->stmt->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $this->result = pg_query($conn, $sql);
    }

    if (!$this->result) {
      $error = pg_result_error($this->result);
      throw new Exception("pgsql_query execute failed:{$sql} ERROR:{$error}");
    }
  }

  public function getResultSet()
  {
    return new Sabel_DB_Driver_ResultSet(pg_fetch_all($this->result));
  }
}
