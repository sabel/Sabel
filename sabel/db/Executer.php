<?php

/**
 * Sabel_DB_Executer
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Executer
{
  private
    $property = null,
    $driver   = null,
    $isModel  = false;

  private
    $conditions  = array(),
    $constraints = array();

  public function __construct($param)
  {
    if ($param instanceof Sabel_DB_Property) {
      $this->property = $param;
      $this->isModel  = true;
      $this->initialize($param);
    } else if (is_string($param)) {
      $this->setDriver($param);
    } else {
      $errorMsg = 'Error: Sabel_DB_Executer::__construct() '
                . 'invalid parameter. should be a string or an instance of Sabel_DB_Wrapper';

      throw new Exception($errorMsg);
    }
  }

  public function initialize($property)
  {
    $this->driver = Sabel_DB_Connection::getDriver($property->connectName);
    if ($this->driver instanceof Sabel_DB_Driver_Native_Mssql) {
      $this->driver->setDefaultOrderKey($property->primaryKey);
    }
  }

  public function setCondition($condition)
  {
    if (!$condition instanceof Sabel_DB_Condition)
      throw new Exception('Error: argument should be an instance of Sabel_DB_Condition');

    $this->conditions[$condition->key] = $condition;
  }

  public function getCondition()
  {
    return ($this->isModel) ? $this->property->getCondition() : $this->conditions;
  }

  public function setConstraint($param1, $param2 = null)
  {
    $param = (is_array($param1)) ? $param1 : array($param1 => $param2);
    foreach ($param as $key => $val) {
      if (isset($val)) $this->constraints[$key] = $val;
    }
  }

  public function getConstraint()
  {
    return ($this->isModel) ? $this->property->getConstraint() : $this->constraints;
  }

  public function setDriver($connectName)
  {
    $this->driver = Sabel_DB_Connection::getDriver($connectName);
  }

  public function getDriver()
  {
    return $this->driver;
  }

  public function getStatement()
  {
    return $this->driver->getStatement();
  }

  public function execute()
  {
    $driver = $this->driver;
    $driver->makeQuery($this->getCondition(), $this->getConstraint());
    $this->tryExecute($driver);
    return $driver->getResultSet();
  }

  public function update($table, $data)
  {
    $driver = $this->driver;
    $driver->setUpdateSQL($table, $data);
    $driver->makeQuery($this->getCondition());
    $this->tryExecute($driver);
  }

  public function insert($table, $data, $idColumn)
  {
    try {
      $this->driver->executeInsert($table, $data, $idColumn);
      return $this->driver->getLastInsertId();
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function multipleInsert($table, $data, $idColumn)
  {
    try {
      foreach ($data as $val) $this->driver->executeInsert($table, $val, $idColumn);
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function executeQuery($sql, $param)
  {
    $this->tryExecute($this->driver, $sql, $param);
    return $this->driver->getResultSet();
  }

  public function tryExecute($driver, $sql = null, $param = null)
  {
    try {
      $driver->execute($sql, $param);
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function executeError($errorMsg)
  {
    throw new Exception($errorMsg);
  }
}
