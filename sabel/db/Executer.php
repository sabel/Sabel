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
  protected
    $property    = null,
    $driver      = null,
    $conditions  = array(),
    $constraints = array();

  private
    $isModel     = false;

  public function __construct($properties)
  {
    if (!is_array($properties)) {
      $errorMsg = 'Sabel_DB_Executer::__construct() argument must be an array.';
      throw new Exception($errorMsg);
    }
    $property = new Sabel_DB_Property();
    $property->set($properties);
    $this->property = $property;
    $this->createDriver($property->connectName);
  }

  public function setDriver($property)
  {
    $this->isModel = true;
    $this->createDriver($property->connectName)->extension($property);
  }

  private function createDriver($connectName)
  {
    $this->driver = Sabel_DB_Connection::getDriver($connectName);
    return $this->driver;
  }

  /**
   * setting condition.
   *
   * @param mixed    $arg1 column name ( with the condition prefix ),
   *                       or value of primary key,
   *                       or instance of Sabel_DB_Condition.
   * @param mixed    $arg2 condition value.
   * @param constant $arg3 denial ( Sabel_DB_Condition::NOT )
   * @return void
   */
  public function setCondition($arg1, $arg2 = null, $arg3 = null)
  {
    if (empty($arg1)) return null;

    if (is_object($arg1) || is_array($arg1)) {
      $this->conditions[] = $arg1;
    } else {
      if (is_null($arg2)) {
        $arg3 = null;
        $arg2 = $arg1;
        $arg1 = $this->property->primaryKey;
      }
      $condition = new Sabel_DB_Condition($arg1, $arg2, $arg3);
      $this->conditions[$condition->key] = $condition;
    }
  }

  /**
   * setting constraint.
   * the keys which you can use are 'group', 'having', 'order', 'limit', 'offset'.
   *
   * @param  mixed $arg1 array constraint(s). or string key.
   * @param  mixed $arg2 value of integer or string.
   * @return void
   */
  public function setConstraint($arg1, $arg2 = null)
  {
    if (!is_array($arg1)) $arg1 = array($arg1 => $arg2);

    foreach ($arg1 as $key => $val) {
      if (isset($val)) $this->constraints[$key] = $val;
    }
  }

  public function unsetCondition()
  {
    $this->conditions  = array();
    $this->constraints = array();
  }

  public function getDriver()
  {
    return $this->driver;
  }

  public function getStatement()
  {
    return $this->driver->getStatement();
  }

  public function execution()
  {
    $driver = $this->driver;
    $driver->makeQuery($this->conditions, $this->constraints);
    $this->tryExecute($driver);
    return $driver->getResultSet();
  }

  public function update($table, $data)
  {
    $driver = $this->driver;
    $driver->setUpdateSQL($table, $data);
    $driver->makeQuery($this->conditions);
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

  public function execMultipleInsert($table, $data, $idColumn)
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
    if (Sabel_DB_Transaction::isActive()) Sabel_DB_Transaction::rollback();
    throw new Exception($errorMsg);
  }

  public function getTableNames()
  {
    return $this->createSchemaAccessor()->getTableNames();
  }

  public function getColumnNames($tblName = null)
  {
    if (is_null($tblName)) $tblName = $this->property->table;
    return $this->createSchemaAccessor()->getColumnNames($tblName);
  }

  public function getTableSchema($tblName = null)
  {
    if (is_null($tblName)) $tblName = $this->property->table;
    return $this->createSchemaAccessor()->getTable($tblName);
  }

  public function getAllTableSchema()
  {
    return $this->createSchemaAccessor()->getTables();
  }

  private function createSchemaAccessor()
  {
    $connectName = $this->property->connectName;
    $schemaName  = Sabel_DB_Connection::getSchema($connectName);
    return new Sabel_DB_Schema_Accessor($connectName, $schemaName);
  }

  public function close()
  {
    Sabel_DB_Connection::close($this->property->connectName);
  }

  /**
   * an alias for setCondition.
   *
   * @return void
   */
  public function scond($arg1, $arg2 = null, $not = null)
  {
    $this->setCondition($arg1, $arg2, $not);
  }

  /**
   * an alias for setConstraint.
   *
   * @return void
   */
  public function sconst($arg1, $arg2 = null)
  {
    $this->setConstraint($arg1, $arg2);
  }
}
