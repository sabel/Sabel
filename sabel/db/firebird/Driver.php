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
  public function __construct()
  {
    $this->stmt = new Sabel_DB_Firebird_Statement('firebird');
  }

  public function loadStatement()
  {
    return $this->stmt;
  }

  public function loadTransaction()
  {
    return Sabel_DB_Firebird_Transaction::getInstance();
  }

  public function begin($conName)
  {
    $trans = $this->loadTransaction();

    if (!$trans->isActive($conName)) {
      $conn = Sabel_DB_Connection::getConnection($conName);
      $resource = ibase_trans(IBASE_COMMITTED|IBASE_REC_NO_VERSION, $conn);
      $trans->begin($resource, $conName);
    }
  }

  public function doCommit($conn)   { /*  ignore  */ }
  public function doRollback($conn) { /*  ignore  */ }

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
    $conn = $this->loadTransaction()->get($this->connectName);

    if ($conn === null) {
      $conn = Sabel_DB_Connection::getConnection($this->connectName);
      $autoCommit = true;
    } else {
      $autoCommit = false;
    }

    if ($sql === null && ($sql = $this->stmt->getSQL()) === '')
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');

    $result = ibase_query($conn, $sql);

    if (!$result) {
      $error = ibase_errmsg();
      $sql   = substr($sql, 0, 128) . " ...";
      throw new Exception("ibase_query execute failed:{$sql} ERROR:{$error}");
    }

    $rows = array();
    if (is_resource($result)) {
      while ($row = ibase_fetch_assoc($result)) $rows[] = array_change_key_case($row);
    }

    if ($autoCommit) ibase_commit($conn);

    $this->resultSet = new Sabel_DB_Result_Row($rows);
  }
}
