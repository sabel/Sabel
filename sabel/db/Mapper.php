<?php

/**
 * important class of sabel_db package.
 * inherit this class to use a orm solution.
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
abstract class Sabel_DB_Mapper
{
  const WITH_PARENT = 'WITH_PARENT';

  protected
    $conditions      = array(),
    $selectCondition = array();

  protected
    $constraints         = array(),
    $childConditions     = array(),
    $childConstraints    = array(),
    $defChildConstraints = array(),
    $myChildren          = null;

  protected
    $driver       = null,
    $connectName  = '',
    $cachedParent = array(),
    $table        = '',
    $structure    = 'normal',
    $projection   = '*',
    $defColumn    = 'id';

  protected
    $data         = array(),
    $newData      = array(),
    $jointKey     = array(),
    $parentTables = array(),
    $joinColCache = array(),
    $cascadeStack = array(),
    $selected     = false,
    $withParent   = false,
    $autoNumber   = true;

  public function setDriver($connectName)
  {
    $this->driver = $this->makeDriver($connectName);
  }

  protected function getDriver()
  {
    return $this->driver;
  }

  protected function makeDriver($connectName)
  {
    $this->connectName = $connectName;
    $conn = Sabel_DB_Connection::getConnection($connectName);

    switch (Sabel_DB_Connection::getDriverName($connectName)) {
      case 'pdo':
        $pdoDb = Sabel_DB_Connection::getDB($connectName);
        return new Sabel_DB_Driver_Pdo_Driver($conn, $pdoDb);
      case 'pgsql':
        return new Sabel_DB_Driver_Native_Pgsql($conn);
      case 'mysql':
        return new Sabel_DB_Driver_Native_Mysql($conn);
      case 'firebird':
        return new Sabel_DB_Driver_Native_Firebird($conn);
    }
  }

  public function getConnectName()
  {
    return $this->connectName;
  }

  public function getSchemaName()
  {
    return Sabel_DB_Connection::getSchema($this->connectName);
  }

  public function __construct($param1 = null, $param2 = null)
  {
    if (Sabel_DB_Transaction::isActive()) $this->begin();

    if ($this->table === '') $this->table = strtolower(get_class($this));
    if ($param1 !== '' && !is_null($param1)) $this->defaultSelectOne($param1, $param2);
  }

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
    if ($this->is_selected()) $this->newData[$key] = $val;
  }

  public function __get($key)
  {
    return (isset($this->data[$key])) ? $this->data[$key] : null;
  }

  public function is_selected()
  {
    return $this->selected;
  }

  public function toArray()
  {
    return $this->data;
  }

  public function setProjection($p)
  {
    $this->projection = (is_array($p)) ? implode(',', $p) : $p;
  }

  public function setDefaultColumn($column)
  {
    $this->defColumn = $column;
  }

  public function setJointKey($keys)
  {
    if (!is_array($keys))
      throw new Exception('joint keys are not array.');

    $this->jointKey = $keys;
  }

  public function setTableName($table)
  {
    $this->table = $table;
  }

  public function getTableName()
  {
    return $this->table;
  }

  public function getTableSchema()
  {
    $schemaClass = $this->connectName . '_' . $this->table;

    if (class_exists($schemaClass, false)) {
      if (!is_object($tSchema = Sabel_DB_SimpleCache::get($schemaClass))) {
        $sc = new $schemaClass();
        $tSchema = new Sabel_DB_Schema_Table($this->table);
        $tSchema->setColumns($sc->get());
        Sabel_DB_SimpleCache::add($schemaClass, $tSchema);
      }
      return $tSchema;
    } else {
      $sa = new Sabel_DB_Schema_Accessor($this->connectName, $this->getSchemaName());
      return $sa->getTable($this->table);
    }
  }

  public function getAllSchema()
  {
    $sa = new Sabel_DB_Schema_Accessor($this->connectName, $this->getSchemaName());
    return $sa->getTables();
  }

  public static function getSchemaAccessor($connectName, $schemaName = null)
  {
    return new Sabel_DB_Schema_Accessor($connectName, $schemaName);
  }

  public function enableParent()
  {
    $this->withParent = true;
  }

  public function disableParent()
  {
    $this->withParent = false;
  }

  public function disableAutoNumber()
  {
    $this->autoNumber = false;
  }

  public function setProperties($array)
  {
    if (!is_array($array))
      throw new Exception('properties Argument is not array.');

    foreach ($array as $key => $val) $this->$key = $val;
  }

  public function getStructure()
  {
    return $this->structure;
  }

  public function getMyChildren()
  {
    return $this->myChildren;
  }

  public function getMyChildConstraint()
  {
    return $this->childConstraints;
  }

  public function __call($method, $parameters)
  {
    @list($paramOne, $paramTwo) = $parameters;
    $this->setCondition($method, $paramOne, $paramTwo);
  }

  public function setConstraint($param1, $param2 = null)
  {
    if (!is_array($param1)) $param1 = array($param1 => $param2);

    foreach ($param1 as $key => $val) {
      if (isset($val)) {
        $this->constraints[$key] = $val;
      } else {
        throw new Exception('Error: setConstraint() constraint value is null.');
      }
    }
  }

  public function setChildConstraint($param1, $param2 = null)
  {
    if (isset($param2) && is_array($param2)) {
      foreach ($param2 as $key => $val)
        $this->childConstraints[$param1][$key] = $val;
    } else if (isset($param2)) {
      $this->defChildConstraints = array($param1 => $param2);
    } else if (is_array($param1)) {
      $this->defChildConstraints = $param1;
    } else {
      throw new Exception('Error: setChildConstraint() when Argument 2 is null, Argument 1 must be an Array');
    }
  }

  public function setChildCondition($key, $val)
  {
    $this->childConditions[$key] = $val;
  }

  /**
   * setting condition
   *
   * @param mixed string or int this value use tow means for
   *          default column value or a condition column name.
   * @param mixed string or int or NULL
   *          this value use three means for
   *          default column value or when has param3 value of special condition
   *          or when has no param3 param2 is value
   * @param mixed string or int or NULL
   *          this value use for value of special condition.
   * @return void
   */
  public function setCondition($param1, $param2 = null, $param3 = null)
  {
    if (empty($param1)) return null;

    if ($this->isSpecialParam($param3, $param1)) {
      if (is_null($param2)) throw new Exception('Error: setCondition() Argument 2 is null.');
      $this->conditions[$param1] = array($param2, $param3);
    } else if ($this->isDefaultColumnValue($param2)) {
      $this->conditions[$this->defColumn] = $param1;
    } else {
      $this->conditions[$param1] = $param2;
    }
  }

  private function isSpecialParam($param3, $param1)
  {
    return (isset($param3) && !is_array($param1));
  }

  private function isDefaultColumnValue($param2)
  {
    return is_null($param2);
  }

  public function unsetCondition()
  {
    $this->conditions = array();
  }

  public function begin()
  {
    return Sabel_DB_Transaction::begin($this->connectName, $this->driver);
  }

  public function commit()
  {
    Sabel_DB_Transaction::commit();
  }

  public function rollback()
  {
    Sabel_DB_Transaction::rollback();
  }

  public function getCount($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setCondition($param1, $param2, $param3);

    $driver = $this->driver;
    $driver->setBasicSQL("SELECT count(*) FROM {$this->table}");
    $driver->makeQuery($this->conditions, array('limit' => 1));

    $this->tryExecute($driver);
    $row = $driver->fetch();
    return (int) $row[0];
  }

  public function getColumnNames($table = null)
  {
    $table = (isset($table)) ? $table : $this->table;

    $this->disableParent();
    $conditions  = array();
    $constraints = array('limit' => 1);

    $this->driver->setBasicSQL("SELECT * FROM {$table}");
    $res = $this->getRecords($this->driver, $conditions, $constraints);

    return array_keys($res[0]->toArray());
  }

  public function getFirst($orderColumn)
  {
    return $this->getMost('ASC', $orderColumn);
  }

  public function getLast($orderColumn)
  {
    return $this->getMost('DESC', $orderColumn);
  }

  protected function getMost($order, $orderColumn)
  {
    $this->setCondition($orderColumn, 'NOT NULL');
    $this->setConstraint(array('limit' => 1, 'order' => "{$orderColumn} {$order}"));
    return $this->selectOne();
  }

  public function aggregate($functions, $child = null)
  {
    if (is_null($child)) {
      $table    = $this->table;
      $idColumn = $this->defColumn;
    } else {
      $table    = $child;
      $idColumn = $this->table . '_id';
    }

    $driver = $this->driver;
    $driver->setAggregateSQL($table, $idColumn, $functions);
    $driver->makeQuery(null, $this->constraints);

    $this->tryExecute($driver);
    $rows = $driver->fetchAll(Sabel_DB_Driver_Const::ASSOC);
    return $this->toObject($rows);
  }

  protected function defaultSelectOne($param1, $param2 = null)
  {
    $this->setCondition($param1, $param2);
    $this->makeFindObject($this);
  }

  public function selectOne($param1 = null, $param2 = null, $param3 = null)
  {
    if (is_null($param1) && is_null($this->conditions))
      throw new Exception('Error: selectOne() [WHERE] must be set condition.');

    $this->addSelectCondition($param1, $param2, $param3);
    return $this->makeFindObject(clone($this));
  }

  protected function makeFindObject($model)
  {
    $driver = $model->driver;
    $driver->setBasicSQL("SELECT {$model->projection} FROM {$model->table}");
    $driver->makeQuery($model->conditions, $model->constraints);

    $model->selectCondition = $model->conditions;

    $this->tryExecute($driver);
    if ($row = $driver->fetch(Sabel_DB_Driver_Const::ASSOC)) {
      if ($model->withParent) $row = $model->selectWithParent($row);

      $model->setSelectedProperty($model, $row);

      if (!is_null($myChild = $model->getMyChildren())) {
        $model->getDefaultChild($myChild, $model);
      }
    } else {
      $model->data = $model->conditions;
    }

    $this->constraints = array();
    $this->conditions  = array();
    return $model;
  }

  private function addSelectCondition($param1, $param2, $param3)
  {
    if ($param1 === self::WITH_PARENT) {
      $this->enableParent();
    } else {
      $this->setCondition($param1, $param2, $param3);
    }
  }

  public function selectJoin($relTableList, $columnList = null)
  {
    if (!is_array($relTableList))
      throw new Exception('Error: joinSelect() argument must be an array.');

    $thisTable     = $this->table;
    $relTableArray = $this->toArrayJoinTables($relTableList);

    $sql = array('SELECT ');
    $columns = (isset($columnList[$thisTable])) ? $columnList[$thisTable] : $this->getColumnNames($thisTable);
    foreach ($columns as $c) array_push($sql, "{$thisTable}.{$c}, ");

    $joinTables = array();
    foreach ($relTableArray as $tables) {
      foreach ($tables as $tbl) {
        if ($tbl !== $thisTable && !isset($this->joinColCache[$tbl])) {
          $joinTables[] = $tbl;
          $this->addJoinColumns($sql, $tbl, $columnList);
        }
      }
    }

    $sql = join('', $sql);
    $sql = array(substr($sql, 0, strlen($sql) - 2));
    array_push($sql, " FROM {$thisTable}");

    foreach ($relTableArray as $tables) array_push($sql, $this->getLeftJoin($tables));

    $driver = $this->driver;
    $driver->setBasicSQL(join('', $sql));
    $driver->makeQuery($this->conditions, $this->constraints);

    $this->tryExecute($driver);
    $rows = $driver->fetchAll(Sabel_DB_Driver_Const::ASSOC);

    $recordObj = array();
    foreach ($rows as $row) {
      $models    = array();
      $acquire   = array();

      foreach ($relTableArray as $tables) {
        foreach ($tables as $t) {
          if ($t !== $thisTable && !isset($acquire[$t])) {
            foreach ($this->joinColCache[$t] as $column) {
              $acquire[$t][$column] = $row["pre_{$t}_{$column}"];
              unset($row["pre_{$t}_{$column}"]);
            }
            $obj = $this->newClass($t);
            $this->setSelectedProperty($obj, $acquire[$t]);
            $models[$t] = $obj;
          }
        }
      }

      $obj = $this->newClass($thisTable);
      $this->setSelectedProperty($obj, $row);
      $models[$thisTable] = $obj;

      foreach ($joinTables as $model) {
        foreach ($relTableArray as $tables) {
          if ($model === $tables['child'] && $thisTable !== $tables['child']) {
            $parent = $tables['parent'];
            $models[$model]->$parent = $models[$parent];
            $models[$model]->newData = array();
          }
        }
      }

      foreach ($relTableArray as $tables) {
        if ($tables['child'] === $thisTable) {
          $parent = $tables['parent'];
          $obj->$parent = $models[$parent];
          $obj->newData = array();
        }
      }
      $recordObj[] = $obj;
    }
    return $recordObj;
  }

  private function toArrayJoinTables($relTableList)
  {
    $relTableArray = array();

    foreach ($relTableList as $tables) {
      $split = explode(':', $tables);

      $rel = array();
      $rel['child']    = $split[0];
      $rel['parent']   = $split[1];
      $relTableArray[] = $rel;
    }
    return $relTableArray;
  }

  private function addJoinColumns(&$sql, $table, $columnList = null)
  {
    $joinCol = array();
    $columns = (isset($columnList[$table])) ? $columnList[$table] : $this->getColumnNames($table);
    foreach ($columns as $c) {
      $joinCol[] = $c;
      array_push($sql, "{$table}.{$c} AS pre_{$table}_{$c}, ");
    }
    $this->joinColCache[$table] = $joinCol;
  }

  private function getLeftJoin($tables)
  {
    $c = $tables['child'];
    $p = $tables['parent'];

    return " LEFT JOIN {$p} ON {$c}.{$p}_id = {$p}.id ";
  }

  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    $this->addSelectCondition($param1, $param2, $param3);

    $driver = $this->driver;
    $driver->setBasicSQL("SELECT {$this->projection} FROM {$this->table}");
    return $this->getRecords($driver, $this->conditions, $this->constraints);
  }

  public function getChild($child, $model = null)
  {
    if (is_null($model)) $model = $this;

    $this->chooseMyChildConstraint($child, $model);

    if (is_null($model->childConstraints[$child]['limit']))
      throw new Exception('Error: getChildren() must be set limit constraints');

    $model->childConditions["{$model->table}_id"] = $model->data[$model->defColumn];

    $driver = $this->newClass($child)->getDriver();
    $driver->setBasicSQL("SELECT {$model->projection} FROM {$child}");

    $conditions  = $model->childConditions;
    $constraints = $model->childConstraints[$child];

    if ($children = $model->getRecords($driver, $conditions, $constraints, $child)) {
      $model->data[$child] = $children;
      return $children;
    } else {
      return false;
    }
  }

  protected function getRecords($driver, &$conditions, &$constraints = null, $child = null)
  {
    $driver->makeQuery($conditions, $constraints);
    $this->tryExecute($driver);

    $rows = $driver->fetchAll(Sabel_DB_Driver_Const::ASSOC);
    if (!$rows) return false;

    $recordObj = array();
    foreach ($rows as $row) {
      if (is_null($child)) {
        $model = $this->newClass($this->table);
        $withParent = $this->withParent;
        if ($this->childConstraints) $model->childConstraints = $this->childConstraints;
      } else {
        $model = $this->newClass($child);
        $withParent = ($this->withParent) ? true : $model->withParent;
      }

      if ($withParent)
        $row = $this->selectWithParent($row);

      $this->setSelectedProperty($model, $row);

      if (!is_null($myChild = $model->getMyChildren())) {
        if (isset($child)) $this->chooseMyChildConstraint($myChild, $model);
        $this->getDefaultChild($myChild, $model);
      }
      $recordObj[] = $model;
    }

    $constraints = array();
    $conditions  = array();
    $this->childConditions  = array();
    $this->childConstraints = array();

    return $recordObj;
  }

  protected function getDefaultChild($children, $model)
  {
    if (!is_array($children)) $children = array($children);

    foreach ($children as $val) {
      $this->chooseMyChildConstraint($val, $model);
      $model->getChild($val, $model);
    }
  }

  private function chooseMyChildConstraint($child, $model)
  {
    if (array_key_exists($child, $this->childConstraints)) {
      $constraints = $this->childConstraints[$child];
    } else if ($this->defChildConstraints) {
      $constraints = $this->defChildConstraints;
    } else if (isset($model->childConstraints[$child])) {
      $constraints = $model->childConstraints[$child];
    } else {
      $constraints = $this->hasDefaultChildConstraint($model);
    }

    $model->setChildConstraint($child, $constraints);
    $model->defChildConstraints = $this->defChildConstraints;
  }

  private function hasDefaultChildConstraint($model)
  {
    if ($model->defChildConstraints) {
      return $model->defChildConstraints;
    } else {
      throw new Exception('Error: limit constraint of child object, not found.');
    }
  }

  protected function selectWithParent($row)
  {
    $this->parentTables = array($this->table);
    foreach ($row as $key => $val) {
      if (strpos($key, '_id') !== false) {
        $table = str_replace('_id', '', $key);
        $row[$table] = $this->addParentObject($table, $val);
      }
    }
    return $row;
  }

  protected function addParentObject($table, $id)
  {
    if ($this->getStructure() !== 'tree' && $this->isAcquired($table)) return null;

    $model = $this->newClass($table);
    if (is_null($id)) return $model;

    if (!is_array($row = Sabel_DB_SimpleCache::get($table . $id))) {
      $driver = $model->getDriver();
      $driver->setBasicSQL("SELECT {$model->projection} FROM {$table}");
      $driver->makeQuery(array($model->defColumn => $id));

      $this->tryExecute($driver);
      $row = $driver->fetch(Sabel_DB_Driver_Const::ASSOC);
      if (!$row) {
        $model->selected = true;
        $model->id = $id;
        return $model;
      }
      Sabel_DB_SimpleCache::add($table . $id, $row);
    }

    foreach ($row as $key => $val) {
      if (strpos($key, '_id') !== false) {
        $key = str_replace('_id', '', $key);
        $row[$key] = $this->addParentObject($key, $val);
      } else {
        $row[$key] = $val;
      }
    }
    $this->setSelectedProperty($model, $row);
    $model->newData = array();
    return $model;
  }

  private function setSelectedProperty($model, $row)
  {
    if (empty($model->jointKey)) {
      $value = (isset($row[$model->defColumn])) ? $row[$model->defColumn] : null;
      $model->selectCondition[$model->defColumn] = $value;
    } else {
      foreach ($model->jointKey as $key) $model->selectCondition[$key] = $row[$key];
    }
    $model->setProperties($row);
    $model->selected = true;
  }

  private function isAcquired($table)
  {
    if (in_array($table, $this->parentTables)) return true;
    $this->parentTables[] = $table;
    return false;
  }

  public function newChild($child = null)
  {
    $id = $this->data[$this->defColumn];
    if (empty($id)) throw new Exception('Error: who is a parent? hasn\'t id value.');

    $parent = strtolower(get_class($this));
    $table  = (is_null($child)) ? $parant : $child;
    $model  = $this->newClass($table);

    $column = $parent . '_id';
    $model->$column = $id;
    return $model;
  }

  protected function newClass($name)
  {
    if ($this->mapper_class_exists($name)) {
      return new $name();
    } else {
      return new Sabel_DB_Basic($name);
    }
  }

  private function mapper_class_exists($className)
  {
    return (class_exists($className, false) && strtolower($className) !== 'sabel_db_basic');
  }

  public function clearChild($child)
  {
    if (isset($this->data[$this->defColumn])) {
      $id = $this->data[$this->defColumn];
    } else {
      throw new Exception('Error: who is a parent? hasn\'t id value.');
    }

    $driver = $this->newClass($child)->getDriver();
    $driver->setBasicSQL("DELETE FROM {$child}");
    $driver->makeQuery(array("{$this->table}_id" => $id));

    $this->tryExecute($driver);
    $this->conditions  = array();
    $this->constraints = array();
  }

  public function save($data = null)
  {
    if (isset($data) && !is_array($data))
      throw new Exception('Error: Argument must be an Array');

    if ($this->is_selected()) {
      if ($data) $this->newData = $data;
      return $this->update();
    } else {
      if ($data) $this->data = $data;
      return $this->insert();
    }
  }

  public function allUpdate($data)
  {
    $driver = $this->driver;
    $driver->setUpdateSQL($this->table, $data);
    $driver->makeQuery($this->conditions);

    $this->tryExecute($driver);
    $this->conditions = array();
  }

  protected function update()
  {
    $driver = $this->driver;
    $driver->setUpdateSQL($this->table, $this->newData);
    $driver->makeQuery($this->selectCondition);

    $this->tryExecute($driver);
    $this->selectCondition = array();
  }

  protected function insert()
  {
    try {
      $idColumn = ($this->autoNumber) ? $this->defColumn : false;
      $this->driver->executeInsert($this->table, $this->data, $idColumn);
      return $this->driver->getLastInsertId();
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function multipleInsert($data)
  {
    if (!is_array($data)) throw new Exception('Error: data is not array.');

    $result = $this->begin();
    try {
      foreach ($data as $val)
        $this->driver->executeInsert($this->table, $val, $this->defColumn);

      if ($result) $this->commit();
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function remove($param1 = null, $param2 = null, $param3 = null)
  {
    $idValue = null;

    if (isset($this->selectCondition[$this->defColumn]))
      $idValue = $this->selectCondition[$this->defColumn];

    if (is_null($param1) && empty($this->conditions) && is_null($idValue))
      throw new Exception("Error: remove() [WHERE] must be set condition");

    if (isset($param1)) {
      $this->setCondition($param1, $param2, $param3);
    } else {
      $this->setCondition($this->defColumn, $idValue);
    }

    $driver = $this->driver;
    $driver->setBasicSQL("DELETE FROM {$this->table}");
    $driver->makeQuery($this->conditions);

    $this->tryExecute($driver);
    $this->conditions  = array();
    $this->constraints = array();
  }

  public function cascadeDelete($id = null)
  {
    if (is_null($id) && !$this->is_selected())
      throw new Exception('Error: need the value of id. or, select the object beforehand.');

    $id = (isset($id)) ? $id : $this->data[$this->defColumn];

    $chain = Cascade_Chain::get();
    $myKey = $this->connectName . ':' . $this->table;

    if (!array_key_exists($myKey, $chain)) {
      throw new Exception('cascade chain is not found. try remove()');
    } else {
      $begin  = $this->begin();
      $models = array();
      foreach ($chain[$myKey] as $chainModel) {
        if ($model = $this->getChainModel($chainModel, "{$this->table}_id", $id)) $models[] = $model;
      }

      foreach ($models as $children) $this->_cascade($children, $chain);

      $this->clearCascadeStack(array_reverse($this->cascadeStack));
      $this->remove($this->defColumn, $id);
      if ($begin) $this->commit();
    }
  }

  protected function _cascade($children, &$chain)
  {
    $table    = $children[0]->getTableName();
    $chainKey = $children[0]->getConnectName() . ':' . $table;

    if (array_key_exists($chainKey, $chain)) {
      $references = array();
      foreach ($chain[$chainKey] as $chainModel) {
        $models = array();
        foreach ($children as $child) {
          if ($model = $this->getChainModel($chainModel, "{$table}_id", $child->id)) $models[] = $model;
        }
        $references[] = $models;
      }
      unset($chain[$chainKey]);

      foreach ($references as $models) {
        foreach ($models as $children) {
          $this->_cascade($children, $chain);
        }
      }
    }
  }

  private function getChainModel($chainValue, $foreign, $id)
  {
    $param  = explode(':', $chainValue);
    $cName  = $param[0];
    $tName  = $param[1];
    $model  = $this->newClass($tName);
    $model->setDriver($cName);
    $models = $model->select($foreign, $id);

    if ($models)
      $this->cascadeStack[$cName.':'.$tName.':'.$id] = $foreign;

    return $models;
  }

  private function clearCascadeStack($stack)
  {
    foreach ($stack as $param => $foreign) {
      $splited = explode(':', $param);
      $cName   = $splited[0];
      $tName   = $splited[1];
      $model   = $this->newClass($tName);
      $model->setDriver($cName);
      $model->remove($foreign, $splited[2]);
    }
  }

  public function execute($sql)
  {
    $this->tryExecute($this->driver, $sql);
    $rows = $this->driver->fetchAll(Sabel_DB_Driver_Const::ASSOC);
    return $this->toObject($rows);
  }

  protected function toObject($rows)
  {
    if (empty($rows)) return null;

    $recordObj = array();
    $model = $this->newClass($this->table);
    foreach ($rows as $row) {
      $cloned = clone($model);
      $cloned->setProperties($row);
      $recordObj[] = $cloned;
    }
    return $recordObj;
  }

  protected function tryExecute($driver, $sql = null)
  {
    try {
      $driver->execute($sql);
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  protected function executeError($errorMsg)
  {
    if (Sabel_DB_Transaction::isActive()) Sabel_DB_Transaction::rollback();
    throw new Exception($errorMsg);
  }

  /**
   * Alias of setConstraint()
   *
   */
  public function sconst($param1, $param2 = null)
  {
    $this->setConstraint($param1, $param2);
  }

  /**
   * Alias of setChildConstraint()
   *
   */
  public function cconst($param1, $param2 = null)
  {
    $this->setChildConstraint($param1, $param2);
  }

  /**
   * Alias of setChildCondition()
   *
   */
  public function ccond($key, $val)
  {
    $this->setChildCondition($key, $val);
  }
}

class Sabel_DB_SimpleCache
{
  private static $cache = array();

  public static function add($key, $val)
  {
    self::$cache[$key] = $val;
  }

  public static function get($key)
  {
    return (isset(self::$cache[$key])) ? self::$cache[$key] : null;
  }
}
