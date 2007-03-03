<?php

//Sabel::using('Sabel_DB_Result_Row');
//Sabel::using('Sabel_DB_General_Statement');

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
    $stmt         = null,
    $resultSet    = null,
    $connectName  = null,
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

  public function setConnectionName($connectName)
  {
    $this->connectName = $connectName;
  }

  public function getConnection()
  {
    return Sabel_DB_Connection::getConnection($this->connectName);
  }

  public function loadTransaction()
  {
    //Sabel::using('Sabel_DB_General_Transaction');
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

  public function select($table, $projection, $conditions = null, $constraints = null)
  {
    $this->stmt->setBasicSQL("SELECT $projection FROM $table");
    $this->makeQuery($conditions, $constraints);
    $this->execute();
  }

  public function selectQuery($query, $conditions, $constraints)
  {
    $this->stmt->setBasicSQL($query);
    $this->makeQuery($conditions, $constraints);
    $this->execute();
  }

  protected function makeQuery($conditions, $constraints = null)
  {
    $this->stmt->makeConditionQuery($conditions);
    if ($constraints) $this->stmt->makeConstraintQuery($constraints);
  }

  public function update($table, $data, $conditions = null)
  {
    $this->makeUpdateQuery($table, $data, $conditions);
    $this->execute();
  }

  protected function makeUpdateQuery($table, $data, $conditions = null)
  {
    $this->stmt->makeUpdateSQL($table, $data);
    if ($conditions) $this->makeQuery($conditions);
  }

  public function insert($table, $data, $idColumn)
  {
    $this->makeInsertQuery($table, $data, $idColumn);
    $this->execute();
  }

  protected function makeInsertQuery($table, $data, $idColumn)
  {
    $data = $this->setIdNumber($table, $data, $idColumn);
    $this->stmt->makeInsertSQL($table, $data);
  }

  public function setIdNumber($table, $data, $idColumn)
  {
    return $data;
  }

  public function arrayInsert($table, $data, $idColumn)
  {
    foreach ($data as $values) {
      $this->insert($table, $values, $idColumn);
    }
  }

  public function getLastInsertId()
  {
    return (isset($this->lastInsertId)) ? $this->lastInsertId : null;
  }

  public function delete($table, $conditions = null)
  {
    $this->stmt->setBasicSQL("DELETE FROM $table");
    if ($conditions) $this->makeQuery($conditions);
    $this->execute();
  }

  public function execute($sql = null, $param = null)
  {
    if ($sql === null) {
      $this->driverExecute();
    } else {
      if ($param) {
        foreach ($param as $key => $val) $param[$key] = $this->stmt->escape($val);
        $sql = vsprintf($sql, $param);
      }
      $this->driverExecute($sql);
    }

    $this->stmt->unsetProperties();
  }

  public function getResultSet()
  {
    return $this->resultSet;
  }
}
