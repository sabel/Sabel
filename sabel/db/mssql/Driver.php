<?php

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
  protected
    $escMethod = 'mssql_escape_string';

  private
    $defCol = '';

  public function __construct($conn)
  {
    $this->conn = $conn;
    $this->db   = 'mssql';
  }

  public function extension($property)
  {
    $this->defCol = $property->primaryKey;
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
    $arrayRow  = $resultSet->fetch(Sabel_DB_ResultSet::NUM);
    return (int)$arrayRow[0];
  }

  public function driverExecute($sql = null, $conn = null)
  {
    $conn = ($conn === null) ? $this->conn : $conn;

    if (isset($sql)) {
      $this->result = mssql_query($sql, $conn);
    } elseif (($sql = $this->stmt->getSQL()) === '') {
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
    return new Sabel_DB_ResultSet($rows);
  }
}
