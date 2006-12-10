<?php

Sabel::using('Sabel_DB_Base_Driver');
Sabel::using('Sabel_DB_Firebird_Statement');
Sabel::using('Sabel_DB_Firebird_Transaction');

/**
 * Sabel_DB_Firebird_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage firebird
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Firebird_Driver extends Sabel_DB_Base_Driver
{
  private
    $conName    = '',
    $resultSet  = null;

  public function __construct($conn)
  {
    $this->conn = $conn;
    $this->db   = 'firebird';
  }

  public function extension($tableProp)
  {
    $this->conName = $tableProp->connectName;
  }

  public function loadStatement()
  {
    $this->stmt = new Sabel_DB_Firebird_Statement($this->db);
    return $this->stmt;
  }

  public function begin($conn)
  {
    $trans = ibase_trans(IBASE_COMMITTED|IBASE_REC_NO_VERSION, $conn);
    Sabel_DB_Firebird_Transaction::add($this->conName, $trans);
  }

  public function commit($conn)
  {
    Sabel_DB_Firebird_Transaction::commit();
  }

  public function rollback($conn)
  {
    Sabel_DB_Firebird_Transaction::rollback();
  }

  public function close($conn)
  {
    ibase_close($conn);
  }

  public function setIdNumber($table, $data, $defColumn)
  {
    $genName = strtoupper("{$table}_{$defColumn}_gen");

    if (!isset($data[$defColumn])) {
      $this->driverExecute('SELECT GEN_ID(' . $genName . ', 1) FROM RDB$DATABASE');
      $resultSet = $this->getResultSet();
      $genNum = $resultSet->fetch(Sabel_DB_Result_Row::NUM);
      $data[$defColumn] = $this->lastInsertId = (int)$genNum[0];
    }
    return $data;
  }

  public function driverExecute($sql = null)
  {
    $conn = Sabel_DB_Firebird_Transaction::get($this->conName);

    if ($conn === null) {
      $conn = $this->conn;
      $autoCommit = true;
    } else {
      $autoCommit = false;
    }

    if (isset($sql)) {
      $result = ibase_query($conn, $sql);
    } elseif (($sql = $this->stmt->getSQL()) === '') {
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');
    } else {
      $result = ibase_query($conn, $sql);
    }

    if (!$result) {
      $error = ibase_errmsg();
      throw new Exception("ibase_query execute failed:{$sql} ERROR:{$error}");
    }

    $rows = array();
    if (is_resource($result)) {
      while ($row = ibase_fetch_assoc($result)) $rows[] = array_change_key_case($row);
    }

    if ($autoCommit) ibase_commit($conn);

    $this->resultSet = new Sabel_DB_Result_Row($rows);
  }

  public function getResultSet()
  {
    return $this->resultSet;
  }
}
