<?php

Sabel::using('Sabel_DB_Base_Driver');

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
  public function __construct()
  {
    $this->stmt = new Sabel_DB_General_Statement('mysql', 'mysql_real_escape_string');
  }

  public function begin($conName)
  {
    $trans = $this->loadTransaction();

    if (!$trans->isActive($conName)) {
      $conn = Sabel_DB_Connection::getConnection($conName);
      $this->driverExecute('START TRANSACTION', $conn);
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
    if ($conn === null) {
      $conn = Sabel_DB_Connection::getConnection($this->connectName);
    }

    if ($sql === null && ($sql = $this->stmt->getSQL()) === '')
      throw new Exception('Error: query not exist. execute makeQuery() beforehand');

    $result = mysql_query($sql, $conn);

    if (!$result) {
      $error = mysql_error($conn);
      $sql   = substr($sql, 0, 128) . " ...";
      throw new Exception("mysql_query execute failed: {$sql} ERROR: {$error}");
    }

    $rows = array();
    if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
    }
    $this->resultSet = new Sabel_DB_Result_Row($rows);
  }
}
