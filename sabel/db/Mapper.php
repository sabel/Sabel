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
  protected
    $table        = '',
    $structure    = 'normal',
    $primaryKey   = 'id',
    $incrementKey = 'id',
    $jointKey     = array();

  protected
    $connectName  = 'default',
    $autoNumber   = true,
    $withParent   = false,
    $projection   = '*',
    $myChildren   = null;

  private
    $executer = null;

  private
    $conditions       = array(),
    $selectCondition  = array(),
    $constraints      = array(),
    $childConditions  = array(),
    $childConstraints = array();

  private
    $data     = array(),
    $newData  = array(),
    $selected = false;

  private
    $parentTables = array(),
    $joinColCache = array(),
    $relational   = array(),
    $cascadeStack = array();

  protected
    $defChildConstraints = array();

  public function __construct($param1 = null, $param2 = null)
  {
    if ($this->table === '') $this->setTableName();
    if (Sabel_DB_Transaction::isActive()) $this->begin();
    if ($param1 !== '' && !is_null($param1)) $this->defaultSelectOne($param1, $param2);
  }

  public function setTableName($tblName = null)
  {
    if (isset($tblName)) {
      $this->table = $tblName;
    } else {
      $this->table = $this->convertToTableName(get_class($this));
    }
  }

  private function convertToTableName($mdlName)
  {
    return substr(strtolower(preg_replace('/([A-Z])/', '_$1', $mdlName)), 1);
  }

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
    if ($this->isSelected()) $this->newData[$key] = $val;
  }

  public function __get($key)
  {
    return (isset($this->data[$key])) ? $this->data[$key] : null;
  }

  public function __call($method, $parameters)
  {
    @list($paramOne, $paramTwo) = $parameters;
    $this->setCondition($method, $paramOne, $paramTwo);
  }

  public function setProperties($array)
  {
    if (!is_array($array)) throw new Exception('properties Argument is not array.');
    foreach ($array as $key => $val) $this->$key = $val;
  }

  public function setConnectName($connectName)
  {
    $this->connectName = $connectName;
  }

  public function getConnectName()
  {
    return $this->connectName;
  }

  public function enableAutoNumber()
  {
    $this->autoNumber = true;
  }

  public function disableAutoNumber()
  {
    $this->autoNumber = false;
  }

  public function isAutoNumber()
  {
    return $this->autoNumber;
  }

  public function setProjection($p)
  {
    $this->projection = (is_array($p)) ? implode(',', $p) : $p;
  }

  public function setMyChildren($children)
  {
    $this->myChildren = $children;
  }

  public function enableParent()
  {
    $this->withParent = true;
  }

  public function disableParent()
  {
    $this->withParent = false;
  }

  public function unsetNewData()
  {
    $this->newData = array();
  }

  public function toArray()
  {
    return $this->data;
  }

  public function isSelected()
  {
    return $this->selected;
  }

  public function schema($tblName = null)
  {
    if (is_null($tblName)) $tblName = $this->table;
    $columns = $this->getTableSchema($tblName)->getColumns();

    if ($tblName === $this->table) {
      foreach ($this->data as $name => $data) {
        if (!is_object($this->data[$name]) && isset($this->data[$name])) {
          $columns[$name]->value = $data;
        }
      }
    }
    return $columns;
  }

  public function getColumnNames($tblName = null)
  {
    if (is_null($tblName)) $tblName = $this->table;
    return $this->createSchemaAccessor()->getColumnNames($tblName);
  }

  public function getTableSchema($tblName = null)
  {
    if (is_null($tblName)) $tblName = $this->table;
    return $this->createSchemaAccessor()->getTable($tblName);
  }

  public function getAllSchema()
  {
    return $this->createSchemaAccessor()->getTables($this->table);
  }

  protected function createSchemaAccessor()
  {
    $connectName = $this->connectName;
    $schemaName  = Sabel_DB_Connection::getSchema($connectName);
    return new Sabel_DB_Schema_Accessor($connectName, $schemaName);
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
    return $this->constraints;
  }

  public function setChildConstraint($param1, $param2 = null)
  {
    if (isset($param2) && is_array($param2)) {
      foreach ($param2 as $key => $val) $this->childConstraints[$param1][$key] = $val;
    } else if (isset($param2)) {
      $this->defChildConstraints = array($param1 => $param2);
    } else if (is_array($param1)) {
      $this->defChildConstraints = $param1;
    } else {
      $error = 'Error: setChildConstraint() '
             . 'when second argument is null, first argument must be an array.';

      throw new Exception($error);
    }
  }

  public function setChildCondition($key, $val = null, $not = null)
  {
    if ($key instanceof Sabel_DB_Condition || is_array($key)) {
      $this->childConditions[] = $key;
    } else {
      $condition = new Sabel_DB_Condition($key, $val, $not);
      $this->childConditions[] = $condition;
    }
  }

  public function setCondition($param1, $param2 = null, $param3 = null)
  {
    if (empty($param1)) return null;

    if ($param1 instanceof Sabel_DB_Condition || is_array($param1)) {
      $this->conditions[] = $param1;
    } else {
      if (is_null($param2)) {
        $param3 = null;
        $param2 = $param1;
        $param1 = $this->primaryKey;
      }
      $this->conditions[] = new Sabel_DB_Condition($param1, $param2, $param3);
    }
  }

  public function getCondition()
  {
    return $this->conditions;
  }

  public function unsetCondition()
  {
    $this->conditions  = array();
    $this->constraints = array();
  }

  public function unsetChildCondition()
  {
    $this->childConditions  = array();
    $this->childConstraints = array();
  }

  public function begin()
  {
    $connectName = $this->connectName;
    $driver = $this->getExecuter()->getDriver();

    $check = true;
    if (Sabel_DB_Connection::getDB($connectName) === 'mysql')
      $check = $driver->checkTableEngine($this->table);

    if ($check) Sabel_DB_Transaction::begin($connectName, $driver);
  }

  public function commit()
  {
    Sabel_DB_Transaction::commit();
  }

  public function rollback()
  {
    Sabel_DB_Transaction::rollback();
  }

  public function close()
  {
    Sabel_DB_Connection::close($this->connectName);
  }

  public function getCount($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setCondition($param1, $param2, $param3);
    $this->setConstraint('limit', 1);

    $this->getStatement()->setBasicSQL('SELECT count(*) FROM ' . $this->table);
    $resultSet = $this->getExecuter()->execute();
    $arrayRow  = $resultSet->fetch(Sabel_DB_Driver_ResultSet::NUM);
    return (int)$arrayRow[0];
  }

  public function getFirst($orderColumn)
  {
    return $this->getMost('ASC', $orderColumn);
  }

  public function getLast($orderColumn)
  {
    return $this->getMost('DESC', $orderColumn);
  }

  private function getMost($order, $orderColumn)
  {
    $this->setCondition($orderColumn, Sabel_DB_Condition::NOTNULL);
    $this->setConstraint(array('limit' => 1, 'order' => "$orderColumn $order"));
    return $this->selectOne();
  }

  public function aggregate($func, $child = null, $group = null)
  {
    if (is_null($child)) {
      $table   = $this->table;
      $columns = (is_null($group)) ? $this->primaryKey : $group;
    } else {
      $table   = $child;
      $columns = (is_null($group)) ? $this->table . '_id' : $group;
    }
    $this->setConstraint('group', $columns);

    $this->getStatement()->setBasicSQL("SELECT $columns , $func FROM $table");
    return $this->toObject($this->getExecuter()->execute());
  }

  protected function defaultSelectOne($param1, $param2 = null)
  {
    $this->setCondition($param1, $param2);
    $this->makeFindObject($this);
  }

  public function selectOne($param1 = null, $param2 = null, $param3 = null)
  {
    if (is_null($param1) && empty($this->conditions))
      throw new Exception('Error: selectOne() [WHERE] must be set condition.');

    $this->setCondition($param1, $param2, $param3);
    return $this->makeFindObject(clone($this));
  }

  protected function makeFindObject($model)
  {
    $model->selectCondition = $model->conditions;
    $model->getStatement()->setBasicSQL("SELECT {$model->projection} FROM " . $model->table);
    $resultSet = $model->getExecuter()->execute();

    if ($row = $resultSet->fetch()) {
      $model->mapping($model, ($model->withParent) ? $this->addParent($row) : $row);
      if (!is_null($myChild = $model->myChildren)) $model->getDefaultChild($myChild, $model);
    } else {
      foreach ($model->conditions as $condition) $model->data[$condition->key] = $condition->value;
    }
    return $model;
  }

  public function selectJoin($relTableList, $columnList = null)
  {
    if (!is_array($relTableList))
      throw new Exception('Error: joinSelect() argument must be an array.');

    $sql        = array('SELECT ');
    $joinTables = array();
    $myTable    = $this->table;
    $relTables  = $this->toTablePair($relTableList);

    $columns = (isset($columnList[$myTable])) ? $columnList[$myTable] : $this->getColumnNames($myTable);
    foreach ($columns as $column) array_push($sql, "{$myTable}.{$column}, ");

    foreach ($relTables as $pair) $joinTables = array_merge($joinTables, array_values($pair));
    $joinTables = array_diff(array_unique($joinTables), (array)$myTable);

    foreach ($joinTables as $table) $this->addJoinColumns($sql, $table, $columnList);

    $sql = array(substr(join('', $sql), 0, -2));
    array_push($sql, " FROM {$myTable}");

    foreach ($relTables as $pair) {
      list($child, $parent) = array_values($pair);
      array_push($sql, " LEFT JOIN $parent ON {$child}.{$parent}_id = {$parent}.id ");
    }

    $this->getStatement()->setBasicSQL(join('', $sql));
    $resultSet = $this->getExecuter()->execute();
    if ($resultSet->isEmpty()) return false;

    $results = array();
    foreach ($resultSet as $row) {
      list($self, $models) = $this->makeEachModels($row, $joinTables);
      $relational = $this->relational;

      foreach ($joinTables as $table) {
        if (!array_key_exists($table, $relational)) continue;
        foreach ($relational[$table] as $parent) {
          $models[$table]->$parent = $models[$parent];
          $models[$table]->unsetNewData();
        }
      }

      foreach ($relational[$myTable] as $parent) $self->$parent = $models[$parent];
      $self->unsetNewData();
      $results[] = $self;
    }
    return $results;
  }

  private function toTablePair($relTableList)
  {
    $relTables = array();

    foreach ($relTableList as $pair) {
      list($child, $parent) = explode(':', $pair);
      $this->relational[$child][] = $parent;
      $relTables[] = array($child, $parent);
    }
    return $relTables;
  }

  private function addJoinColumns(&$sql, $table, $columnList = null)
  {
    $columns = (isset($columnList[$table])) ? $columnList[$table] : $this->getColumnNames($table);
    foreach ($columns as $column) {
      $this->joinColCache[$table][] = $column;
      array_push($sql, "{$table}.{$column} AS pre_{$table}_{$column}, ");
    }
  }

  private function makeEachModels($row, $joinTables)
  {
    $models   = array();
    $acquire  = array();
    $colCache = $this->joinColCache;

    foreach ($joinTables as $table) {
      foreach ($colCache[$table] as $column) {
        $preCol = "pre_{$table}_{$column}";
        $acquire[$table][$column] = $row[$preCol];
        unset($row[$preCol]);
      }
      $model = $this->newClass($table);
      $this->mapping($model, $acquire[$table]);
      $models[$table] = $model;
    }

    $model = $this->newClass($this->table);
    $this->mapping($model, $row);
    $models[$this->table] = $model;
    return array($model, $models);
  }

  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setCondition($param1, $param2, $param3);
    $this->getStatement()->setBasicSQL("SELECT {$this->projection} FROM {$this->table}");
    return $this->getRecords($this);
  }

  protected function getRecords($model, $child = null)
  {
    $resultSet = $model->getExecuter()->execute();
    if ($resultSet->isEmpty()) return false;

    $models = array();
    foreach ($resultSet as $row) {
      if (is_null($child)) {
        $model = $this->newClass($this->table);
        $withParent = $this->withParent;
        if ($this->childConstraints) $model->childConstraints = $this->childConstraints;
      } else {
        $model = $this->newClass($child);
        $withParent = ($this->withParent) ? true : $model->withParent;
      }

      $this->mapping($model, ($withParent) ? $this->addParent($row) : $row);
      if (!is_null($myChild = $model->myChildren)) {
        if (isset($child)) $this->chooseChildConstraint($myChild, $model);
        $this->getDefaultChild($myChild, $model);
      }
      $models[] = $model;
    }
    return $models;
  }

  protected function addParent($row)
  {
    $this->parentTables = array($this->table);
    return $this->checkRelationalColumn($row);
  }

  protected function addParentModels($tblName, $id)
  {
    $tblName = strtolower($tblName);
    if ($this->structure !== 'tree' && $this->isAcquired($tblName)) return false;

    $model = $this->newClass($tblName);
    if (is_null($id)) return $model;

    if (!is_array($row = Sabel_DB_SimpleCache::get($tblName. $id))) {
      $model->setCondition($model->primaryKey, $id);
      $model->getStatement()->setBasicSQL("SELECT {$model->projection} FROM $tblName");
      $resultSet = $model->getExecuter()->execute();

      if (!$row = $resultSet->fetch())
        throw new Exception('Error: relational error. parent does not exists.');

      Sabel_DB_SimpleCache::add($tblName. $id, $row);
    }
    $row = $this->checkRelationalColumn($row);

    $this->mapping($model, $row);
    $model->unsetNewData();
    return $model;
  }

  private function checkRelationalColumn($row)
  {
    foreach ($row as $key => $val) {
      if (strpos($key, '_id') !== false) {
        $tblName = str_replace('_id', '', $key);
        $mdlName = array_map('ucfirst', explode('_', $tblName));
        $result  = $this->addParentModels($tblName, $val);
        if ($result) $row[join('', $mdlName)] = $result;
      }
    }
    return $row;
  }

  private function isAcquired($tblName)
  {
    if (in_array($tblName, $this->parentTables)) return true;
    $this->parentTables[] = $tblName;
    return false;
  }

  public function getChild($child, $model = null)
  {
    if (is_null($model)) $model = $this;

    $class = $this->newClass($child);
    $class->getStatement()->setBasicSQL("SELECT {$class->projection} FROM {$class->table}");

    $this->chooseChildConstraint($child, $model);
    $model->setChildCondition("{$model->table}_id", $model->data[$model->primaryKey]);

    $class->conditions  = $model->childConditions;
    $class->constraints = $model->childConstraints[$child];

    $children = $model->getRecords($class, $child);
    if ($children) $model->$child = $children;
    return $children;
  }

  protected function getDefaultChild($children, $model)
  {
    foreach (is_string($children) ? (array)$children : $children as $val) {
      $this->chooseChildConstraint($val, $model);
      $model->getChild($val, $model);
    }
  }

  private function chooseChildConstraint($child, $model)
  {
    $thisDefault = $this->defChildConstraints;

    if (array_key_exists($child, $this->childConstraints)) {
      $constraints = $this->childConstraints[$child];
    } else if ($thisDefault) {
      $constraints = $thisDefault;
    } else if (isset($model->childConstraints[$child])) {
      $constraints = $model->childConstraints[$child];
    } else {
      $constraints = $model->defChildConstraints;
    }
    if ($constraints) $model->setChildConstraint($child, $constraints);
    if ($thisDefault) $model->defChildConstraints = $thisDefault;
  }

  protected function mapping($model, $row)
  {
    $primaryKey = $model->primaryKey;

    if (empty($model->jointKey)) {
      if (isset($row[$primaryKey])) {
        $condition = new Sabel_DB_Condition($primaryKey, $row[$primaryKey]);
        $model->selectCondition[$primaryKey] = $condition;
      }
    } else {
      foreach ($model->jointKey as $key) {
        $condition = new Sabel_DB_Condition($key, $row[$key]);
        $model->selectCondition[$key] = $condition;
      }
    }
    $model->setProperties($row);
    $model->selected = true;
  }

  public function newChild($child = null)
  {
    $id = $this->data[$this->primaryKey];
    if (empty($id)) throw new Exception('Error: newChild() who is a parent? hasn\'t id value.');

    $parent = strtolower(get_class($this));
    $table  = (is_null($child)) ? $parant : $child;
    $model  = $this->newClass($table);

    $column = $parent . '_id';
    $model->$column = $id;
    return $model;
  }

  protected function newClass($name)
  {
    $model = str_replace('_', '', $name);

    if ($this->modelExists($model)) {
      return new $model();
    } else {
      $class = new Sabel_DB_Basic($this->convertToTableName($name));
      $class->setConnectName($this->connectName);
      return $class;
    }
  }

  private function modelExists($className)
  {
    return (class_exists($className, false) && strtolower($className) !== 'sabel_db_basic');
  }

  public function clearChild($child)
  {
    if (isset($this->data[$this->primaryKey])) {
      $id = $this->data[$this->primaryKey];
    } else {
      throw new Exception('Error: clearChild() who is a parent? hasn\'t id value.');
    }
    $model = $this->newClass($child);

    $model->setCondition($this->table . '_id', $id);
    $model->getStatement()->setBasicSQL('DELETE FROM ' . $model->table);
    $model->getExecuter()->execute();
  }

  public function save($data = null)
  {
    if (!empty($data) && !is_array($data))
      throw new Exception('Error: save() argument must be an array');

    if ($this->isSelected()) {
      $this->conditions = $this->selectCondition;
      $this->getExecuter()->update($this->table, ($data) ? $data : $this->newData);
    } else {
      $data = ($data) ? $data : $this->data;
      return $this->getExecuter()->insert($this->table, $data, $this->checkIncColumn());
    }
  }

  public function allUpdate($data)
  {
    $this->getExecuter()->update($this->table, $data);
  }

  public function multipleInsert($data)
  {
    if (!is_array($data)) throw new Exception('Error: multipleInsert() data is not array.');

    $this->begin();
    try {
      $this->getExecuter()->multipleInsert($this->table, $data, $this->checkIncColumn());
      $this->commit();
    } catch (Exception $e) {
      $this->rollBack();
      throw new Exception($e->getMessage());
    }
  }

  protected function checkIncColumn()
  {
    return ($this->isAutoNumber()) ? $this->incrementKey : false;
  }

  public function remove($param1 = null, $param2 = null, $param3 = null)
  {
    $idValue = null;

    if (isset($this->selectCondition[$this->primaryKey]))
      $idValue = $this->selectCondition[$this->primaryKey]->value;

    if (is_null($param1) && empty($this->conditions) && is_null($idValue))
      throw new Exception("Error: remove() [WHERE] must be set condition");

    if (isset($param1)) {
      $this->setCondition($param1, $param2, $param3);
    } else if (isset($idValue)) {
      $this->setCondition($this->primaryKey, $idValue);
    }

    $this->getStatement()->setBasicSQL('DELETE FROM ' . $this->table);
    $this->getExecuter()->execute();
  }

  public function cascadeDelete($id = null)
  {
    if (is_null($id) && !$this->isSelected())
      throw new Exception('Error: give the value of id or select the object beforehand.');

    $id = (isset($id)) ? $id : $this->data[$this->primaryKey];

    if (!class_exists('Schema_CascadeChain', false))
      throw new Exception('Error: class Schema_CascadeChain does not exist.');

    $chain = Schema_CascadeChain::get();
    $key   = $this->connectName . ':' . $this->table;

    if (!array_key_exists($key, $chain)) {
      throw new Exception('cascade chain is not found. try remove()');
    } else {
      $this->begin();
      $models = array();
      foreach ($chain[$key] as $tblName) {
        $foreignKey = $this->table . '_id';
        if ($model = $this->pushStack($tblName, $foreignKey, $id)) $models[] = $model;
      }

      foreach ($models as $children) $this->getChainModels($children, $chain);

      $this->clearCascadeStack(array_reverse($this->cascadeStack));
      $this->remove($this->primaryKey, $id);
      $this->commit();
    }
  }

  private function getChainModels($children, &$chain)
  {
    $table = $children[0]->table;
    $key   = $children[0]->connectName . ':' . $table;

    if (array_key_exists($key, $chain)) {
      $references = array();
      foreach ($chain[$key] as $tblName) {
        $models = array();
        foreach ($children as $child) {
          $foreignKey = $this->table . '_id';
          if ($model = $this->pushStack($tblName, $foreignKey, $child->id)) $models[] = $model;
        }
        $references[] = $models;
      }
      unset($chain[$key]);

      foreach ($references as $models) {
        foreach ($models as $children) $this->getChainModels($children, $chain);
      }
    }
  }

  private function pushStack($chainValue, $foreignKey, $id)
  {
    list($cName, $tName) = explode(':', $chainValue);
    $model  = $this->newClass($tName);
    $model->setConnectName($cName);
    $models = $model->select($foreignKey, $id);

    if ($models) $this->cascadeStack["{$cName}:{$tName}:{$id}"] = $foreignKey;
    return $models;
  }

  private function clearCascadeStack($stack)
  {
    foreach ($stack as $param => $foreignKey) {
      list($cName, $tName, $idValue) = explode(':', $param);
      $model = $this->newClass($tName);
      $model->setConnectName($cName);
      $model->remove($foreignKey, $idValue);
    }
  }

  public function execute($sql, $param = null)
  {
    if (!empty($param) && !is_array($param))
      throw new Exception('Error: execute() second argument must be an array');

    return $this->toObject($this->getExecuter()->executeQuery($sql, $param));
  }

  protected function toObject($resultSet)
  {
    if ($resultSet->isEmpty()) return false;

    $models = array();
    $model = $this->newClass($this->table);
    foreach ($resultSet as $row) {
      $cloned = clone($model);
      $cloned->setProperties($row);
      $models[] = $cloned;
    }
    return $models;
  }

  private function getExecuter()
  {
    if (isset($this->executer)) return $this->executer;

    $this->executer = new Sabel_DB_Executer($this);
    return $this->executer;
  }

  private function getStatement()
  {
    return $this->getExecuter()->getStatement();
  }

  /**
   * alias for setConstraint()
   */
  public function sconst($param1, $param2 = null)
  {
    $this->setConstraint($param1, $param2);
  }

  /**
   * alias for setChildConstraint()
   */
  public function cconst($param1, $param2 = null)
  {
    $this->setChildConstraint($param1, $param2);
  }

  /**
   * alias of setChildCondition()
   */
  public function ccond($key, $val = null)
  {
    $this->setChildCondition($key, $val);
  }
}
