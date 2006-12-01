<?php

/**
 * Sabel_DB_Driver_Firebird
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage driver
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Firebird extends Sabel_DB_Base_Driver
{
  private $trans = null;

  public function __construct($conn)
  {
    $this->conn = $conn;
    $this->db   = 'firebird';
  }

  // @todo
  public function begin($conn)
  {
    $resource = ibase_trans(IBASE_WRITE, $conn);
    $this->trans = $resource;
    return $resource;
  }

  // @todo
  public function commit($conn)
  {
    if (!ibase_commit($conn)) {
      $error = ibase_errmsg();
      throw new Exception ("Error: transaction commit failed. {$error}");
    }
  }

  // @todo
  public function rollback($conn)
  {
    ibase_rollback($conn);
    unset($this->trans);
  }

  public function close($conn)
  {
    ibase_close($conn);
  }

  public function setIdNumber($table, $data, $defColumn)
  {
    $genName = strtoupper("{$table}_{$defColumn}_gen");

    if (!isset($data[$defColumn])) {
      $this->driverExecute('SELECT GEN_ID(' . $genName . ', 1) FROM rdb$database');
      $resultSet = $this->getResultSet();
      $genNum = $resultSet->fetch(Sabel_DB_Driver_ResultSet::NUM);
      $data[$defColumn] = $this->lastInsertId = (int)$genNum[0];
    }
    return $data;
  }

  public function driverExecute($sql = null)
  {
    $conn = (isset($this->trans)) ? $this->trans : $this->conn;

    if (isset($sql)) {
      $this->result = ibase_query($conn, $sql);
    } elseif (($sql = $this->stmt->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $this->result = ibase_query($conn, $sql);
    }

    if (!$this->result) {
      $error = ibase_errmsg();
      throw new Exception("ibase_query execute failed:{$sql} ERROR:{$error}");
    }
  }

  public function getResultSet()
  {
    $rows   = array();
    $result = $this->result;

    if (is_resource($result)) {
      while ($row = ibase_fetch_assoc($result)) $rows[] = array_change_key_case($row);
    }
    return new Sabel_DB_Driver_ResultSet($rows);
  }
}
