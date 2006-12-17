<?php

Sabel::using('Sabel_DB_Condition');

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
    $tableProp   = null,
    $conditions  = array(),
    $constraints = array();

  protected
    $driver = null;

  /**
   * Sabel_DB_Executer constructor.
   *
   * @param array $properties
   *   ['table']          => table name
   *   ( ['connectName']  => connection name )
   *   ( ['primaryKey']   => primary key )
   *   ( ['incrementKey'] => auto increment column )
   *
   * @return void
   */
  public function __construct($ps)
  {
    $props['table']        = $ps['table'];
    $props['connectName']  = (isset($ps['connectName']))  ? $ps['connectName']  : 'default';
    $props['primaryKey']   = (isset($ps['primaryKey']))   ? $ps['primaryKey']   : 'id';
    $props['incrementKey'] = (isset($ps['incrementKey'])) ? $ps['incrementKey'] : null;

    $this->tableProp = Sabel::load('Sabel_ValueObject', $props);
  }

  /**
   * returns the table name.
   *
   * @return string
   */
  public function getTableName()
  {
    return $this->tableProp->table;
  }

  /**
   * returns the primary key(s).
   *
   * @return mixed string of array
   */
  public function getPrimaryKey()
  {
    return $this->tableProp->primaryKey;
  }
  
  /**
   * set primary key
   *
   * @param string $keyName
   @ @return void
   */
  public function setPrimaryKey($keyName)
  {
    $this->tableProp->primaryKey = $keyName;
  }

  /**
   * returns the connection name.
   *
   * @return string
   */
  public function getConnectName()
  {
    return $this->tableProp->connectName;
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
        $pKey = $this->tableProp->primaryKey;
        if (is_array($pKey)) {
          throw new Exception('Error:setCondition() please specify a column for the condition.');
        }
        $arg3 = null;
        $arg2 = $arg1;
        $arg1 = $pKey;
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
      $this->driver = Sabel_DB_Connection::getDriver($this->getConnectName());
      $this->driver->extension($this->tableProp);
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
    return $this->getDriver()->loadStatement();
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
    $table = $this->tableProp->table;
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
      $this->executeError($e->getMessage(), $driver);
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
      $this->executeError($e->getMessage(), $driver);
    }
  }

  protected function execInsert($driver, $stmt, $data, $idColumn)
  {
    $table = $this->tableProp->table;
    $db    = Sabel_DB_Connection::getDB($this->getConnectName());

    if ($idColumn && ($db === 'pgsql' || $db === 'firebird')) {
      $data = $driver->setIdNumber($table, $data, $idColumn);
    }

    $stmt->makeInsertSQL($table, $data);
    $driver->insert();
  }

  protected function checkIncColumn()
  {
    $incCol = $this->tableProp->incrementKey;
    return (isset($incCol)) ? $incCol : false;
  }

  /**
   * delete row(s)
   *
   * @param  mixed     $param1 column name ( with the condition prefix ), or value of primary key.
   * @param  mixed     $param2 condition value.
   * @param  constrant $param3 denial ( Sabel_DB_Condition::NOT )
   * @return void
   */
  public function delete($arg1 = null, $arg2 = null, $arg3 = null)
  {
    if (empty($this->conditions) && $arg1 === null) {
      $msg  = 'Error:delete() all delete? must be set condition';
      $smpl = 'DELETE FROM {table_name}';
      throw new Exception($msg . " or execute executeQuery('{$smpl}').");
    }

    if ($arg1 !== null) $this->setCondition($arg1, $arg2, $arg3);

    $this->doDelete();
  }

  protected function doDelete()
  {
    $driver = $this->getDriver();
    $this->getStatement()->setBasicSQL('DELETE FROM ' . $this->tableProp->table);
    $driver->makeQuery($this->conditions, $this->constraints);
    $this->tryExecute($driver);
  }

  /**
   * get rows count.
   *
   * @param  mixed    $param1 column name ( with the condition prefix ), or value of primary key.
   * @param  mixed    $param2 condition value.
   * @param  constant $param3 denial ( Sabel_DB_Condition::NOT )
   * @return integer rows count
   */
  public function getCount($arg1 = null, $arg2 = null, $arg3 = null)
  {
    $this->setCondition($arg1, $arg2, $arg3);

    $this->getStatement()->setBasicSQL('SELECT count(*) FROM ' . $this->tableProp->table);
    $row = $this->exec()->fetch(Sabel_DB_Result_Row::NUM);
    return (int)$row[0];
  }

  public function getFirst($orderColumn)
  {
    return $this->getEdge('ASC', $orderColumn);
  }

  public function getLast($orderColumn)
  {
    return $this->getEdge('DESC', $orderColumn);
  }

  protected function getEdge($order, $orderColumn)
  {
    $this->setCondition($orderColumn, Sabel_DB_Condition::NOTNULL);
    $this->setConstraint(array('limit' => 1, 'order' => "$orderColumn $order"));
    $this->getStatement()->setBasicSQL('SELECT * FROM ' . $this->tableProp->table);
    return $this->exec();
  }

  public function executeQuery($sql, $param = null)
  {
    $driver = $this->getDriver();
    $this->tryExecute($driver, $sql, $param);
    return $driver->getResultSet();
  }

  public function tryExecute($driver, $sql = null, $param = null)
  {
    try {
      $driver->execute($sql, $param);
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $driver);
    }
  }

  public function executeError($errorMsg, $driver)
  {
    $driver->rollback();
    throw new Exception($errorMsg);
  }

  public function getTableNames()
  {
    return $this->createSchemaAccessor()->getTableNames();
  }

  public function getColumnNames($tblName = null)
  {
    $tblName = ($tblName === null) ? $this->tableProp->table : $tblName;
    return $this->createSchemaAccessor()->getColumnNames($tblName);
  }

  public function getTableSchema($tblName = null)
  {
    $tblName = ($tblName === null) ? $this->tableProp->table : $tblName;
    return $this->createSchemaAccessor()->getTable($tblName);
  }

  public function getAllTableSchema()
  {
    return $this->createSchemaAccessor()->getTables();
  }

  protected function createSchemaAccessor()
  {
    Sabel::using('Sabel_DB_Schema_Accessor');

    $connectName = $this->getConnectName();
    $schemaName  = Sabel_DB_Connection::getSchema($connectName);
    return new Sabel_DB_Schema_Accessor($connectName, $schemaName);
  }

  public function begin()
  {
    $this->getDriver()->begin($this->getConnectName());
  }

  public function commit()
  {
    $this->getDriver()->commit();
  }

  public function rollback()
  {
    $this->getDriver()->rollback();
  }

  public function close()
  {
    Sabel_DB_Connection::close($this->tableProp->connectName);
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
