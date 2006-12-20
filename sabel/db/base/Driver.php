<?php

Sabel::using('Sabel_DB_Result_Row');
Sabel::using('Sabel_DB_General_Statement');

/**
 * Sabel_DB_Base_Driver
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @subpackage base
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Base_Driver
{
  protected
    $conn = null,
    $stmt = null;

  protected
    $resultSet    = null,
    $lastInsertId = null;

  public abstract function begin($connectName);
  public abstract function doCommit($conn);
  public abstract function doRollback($conn);
  public abstract function close($conn);
  public abstract function driverExecute($sql = null);

  public function extension($obj) { }

  public function loadStatement()
  {
    return $this->stmt;
  }

  public function loadTransaction()
  {
    Sabel::using('Sabel_DB_General_Transaction');
    return Sabel_DB_General_Transaction::getInstance();
  }

  public function commit()
  {
    $this->loadTransaction()->commit();
  }

  public function rollback()
  {
    $this->loadTransaction()->rollback();
  }

  public function update()
  {
    $this->execute();
  }

  public function insert()
  {
    $this->execute();
  }

  public function setIdNumber($table, $data, $idColumn)
  {
    return $data;
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $this->stmt->makeConditionQuery($conditions);
    if ($constraints) $this->stmt->makeConstraintQuery($constraints);
  }

  public function getLastInsertId()
  {
    return (isset($this->lastInsertId)) ? $this->lastInsertId : null;
  }

  public function execute($sql = null, $param = null)
  {
    if ($param) {
      foreach ($param as $key => $val) $param[$key] = $this->stmt->escape($val);
      $sql = vsprintf($sql, $param);
    }

    $this->driverExecute($sql);
    $this->stmt->unsetProperties();
  }

  public function getResultSet()
  {
    return $this->resultSet;
  }
}
