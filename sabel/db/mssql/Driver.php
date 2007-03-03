<?php

//Sabel::using('Sabel_DB_Base_Driver');
//Sabel::using('Sabel_DB_Mssql_Statement');

/**
 * Sabel_DB_Mssql_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage mssql
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mssql_Driver extends Sabel_DB_Base_Driver
{
  private $defCol = '';

  public function __construct()
  {
    $this->stmt = new Sabel_DB_Mssql_Statement('mssql', 'mssql_escape_string');
  }

  public function loadStatement()
  {
    return $this->stmt;
  }

  public function extension($ext)
  {
    $this->defCol = $ext->primaryKey;
    $this->stmt->setDefaultOrderColumn($this->defCol);
  }

  public function begin($conName)
  {
    $trans = $this->loadTransaction();

    if (!$trans->isActive($conName)) {
      $conn = Sabel_DB_Connection::getConnection($conName);
      $this->driverExecute('BEGIN TRANSACTION', $conn);
      $trans->begin($this, $conName);
    }
  }

  public function doCommit($conn)
  {
    $this->driverExecute('COMMIT TRANSACTION', $conn);
  }

  public function doRollback($conn)
  {
    $this->driverExecute('ROLLBACK TRANSACTION', $conn);
  }

  public function close($conn)
  {
    mssql_close($conn);
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $this->stmt->makeConditionQuery($conditions);
    if ($constraints) {
      $constraints['defCol'] = $this->defCol;
      $this->stmt->makeConstraintQuery($constraints);
    }
  }

  public function getLastInsertId()
  {
    $this->driverExecute('SELECT SCOPE_IDENTITY()');
    $resultSet = $this->getResultSet();
    $arrayRow  = $resultSet->fetch(Sabel_DB_Result_Row::NUM);
    return (int)$arrayRow[0];
  }

  public function driverExecute($sql = null, $conn = null)
  {
    if ($conn === null) $conn = $this->getConnection();

    if ($sql === null && ($sql = $this->stmt->getSQL()) === '')
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');

    $result = mssql_query($sql, $conn);

    if (!$result) {
      $error = mssql_get_last_message();
      $sql   = substr($sql, 0, 128) . " ...";
      throw new Exception("mssql_query execute failed:{$sql} ERROR:{$error}");
    }

    $rows = array();
    if (is_resource($result)) {
      while ($row = mssql_fetch_assoc($result)) $rows[] = $row;
    }
    $this->resultSet = new Sabel_DB_Result_Row($rows);
  }
}

function mssql_escape_string($val)
{
  return str_replace("'", "''", $val);
}
