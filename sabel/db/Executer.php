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
    $conditions  = array(),
    $constraints = array();

  protected
    $driver  = null,
    $isModel = false;

  /**
   * Sabel_DB_Executer constructor.
   *
   * @param array $properties
   *   ['table']          => table name
   *   ( ['connectName']  => connection name )
   *   ( ['incrementKey'] => auto increment column )
   *
   * @return void
   */
  public function __construct($properties)
  {
    $this->property = new Sabel_DB_Property();
    $this->property->set($properties);
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
      if ($arg2 === null) {
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

  /**
   * unset condition and constraint.
   *
   * @return void
   */
  public function unsetCondition()
  {
    $this->conditions  = array();
    $this->constraints = array();
  }

  /**
   * create driver instance.
   *
   * @return object
   */
  public function getDriver()
  {
    if ($this->driver === null) {
      $this->driver = Sabel_DB_Connection::getDriver($this->property->connectName);
    }
    return $this->driver;
  }

  /**
   * create statement instance.
   *
   * @return object
   */
  public function getStatement()
  {
    $driver = $this->getDriver();
    return $driver->loadStatement();
  }

  public function exec()
  {
    $driver = $this->getDriver();
    $driver->makeQuery($this->conditions, $this->constraints);
    $this->tryExecute($driver);
    return $driver->getResultSet();
  }

  public function update($data)
  {
    $table = $this->property->table;
    $this->getStatement()->makeUpdateSQL($table, $data);

    $driver = $this->getDriver();
    $driver->makeQuery($this->conditions);
    $driver->update();
  }

  public function insert($data, $incCol = false)
  {
    try {
      $driver = $this->getDriver();
      $stmt   = $this->getStatement();

      $this->execInsert($driver, $stmt, $data, $incCol);
      return $driver->getLastInsertId();
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function ArrayInsert($data)
  {
    try {
      $driver = $this->getDriver();
      $stmt   = $this->getStatement();
      $incCol = $this->checkIncColumn();

      foreach ($data as $val) {
        $this->execInsert($driver, $stmt, $val, $incCol);
      }
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  protected function execInsert($driver, $stmt, $data, $idColumn)
  {
    $table = $this->property->table;
    $db    = Sabel_DB_Connection::getDB($this->property->connectName);

    if ($idColumn && ($db === 'pgsql' || $db === 'firebird')) {
      $data = $driver->setIdNumber($table, $data, $idColumn);
    }

    $stmt->makeInsertSQL($table, $data);
    $driver->insert();
  }

  protected function checkIncColumn()
  {
    $incCol = $this->property->incrementKey;
    return (isset($incCol)) ? $incCol : false;
  }

  public function executeQuery($sql, $param)
  {
    $driver = Sabel_DB_Connection::getDriver($this->property->connectName);
    $this->tryExecute($driver, $sql, $param);
    return $driver->getResultSet();
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
    if ($tblName === null && $this->isModel) return $this->property->getColumns();

    $tblName = ($tblName === null) ? $this->property->table : $tblName;
    return $this->createSchemaAccessor()->getColumnNames($tblName);
  }

  public function getTableSchema($tblName = null)
  {
    if ($tblName === null && $this->isModel) return $this->property->getSchema();

    $tblName = ($tblName === null) ? $this->property->table : $tblName;
    return $this->createSchemaAccessor()->getTable($tblName);
  }

  public function getAllTableSchema()
  {
    return $this->createSchemaAccessor()->getTables();
  }

  protected function createSchemaAccessor()
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
