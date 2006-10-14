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

  //todo public $table = '';
  protected $table = '';
  public $structure    = 'normal';
  public $primaryKey   = 'id';
  public $incrementKey = 'id';
  public $jointKey     = array();

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
    $joinColCache = array(),
    $relational   = array(),
    $cascadeStack = array();

  protected
    $defChildConstraints = array();

  protected
    $connectName = 'default',
    $autoNumber  = true,
    $withParent  = false,
    $projection  = '*',
    $myChildren  = null;

  public function __construct($param1 = null, $param2 = null)
  {
    if ($this->table === '') $this->table = strtolower(get_class($this));
    if (Sabel_DB_Transaction::isActive()) $this->begin();
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

  public function getProjection()
  {
    return $this->projection;
  }

  public function setMyChildren($children)
  {
    $this->myChildren = $children;
  }

  public function getMyChildren()
  {
    return $this->myChildren;
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

  public function is_selected()
  {
    return $this->selected;
  }

  public function getSchemaName()
  {
    return Sabel_DB_Connection::getSchema($this->connectName);
  }

  public function getColumnNames($tblName = null)
  {
    if (is_null($tblName)) $tblName = $this->table;
    return $this->createSchemaAccessor()->getColumnNames($tblName);
  }

  public function getTableSchema()
  {
    return $this->createSchemaAccessor()->getTable($this->table);
  }

  public function getAllSchema()
  {
    return $this->createSchemaAccessor()->getTables($this->table);
  }

  protected function createSchemaAccessor()
  {
    return new Sabel_DB_Schema_Accessor($this->connectName, $this->getSchemaName());
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
      $error  = 'Error: setChildConstraint() ';
      $error .= 'when second argument is null, first argument must be an array.';
      throw new Exception($error);
    }
  }

  public function setChildCondition($key, $val)
  {
    $condition = new Sabel_DB_Condition($key, $val);
    $this->childConditions[$condition->key] = $condition;
  }

  public function setCondition($param1, $param2 = null, $param3 = null)
  {
    if (empty($param1)) return null;

    if (is_null($param2)) {
      $param3 = $param2;
      $param2 = $param1;
      $param1 = $this->primaryKey;
    }

    $condition = new Sabel_DB_Condition($param1, $param2, $param3);
    $this->conditions[$condition->key] = $condition;
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

  protected function getMost($order, $orderColumn)
  {
    $this->setCondition($orderColumn, 'NOT NULL');
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

    $this->addSelectCondition($param1, $param2, $param3);
    return $this->makeFindObject(clone($this));
  }

  protected function makeFindObject($model)
  {
    $model->selectCondition = $model->conditions;

    $projection = $model->getProjection();
    $model->getStatement()->setBasicSQL("SELECT $projection FROM " . $model->table);
    $resultSet = $model->getExecuter()->execute();

    if ($row = $resultSet->fetch()) {
      $model->mapping($model, ($model->withParent) ? $this->addParent($row) : $row);
      if (!is_null($myChild = $model->getMyChildren())) $model->getDefaultChild($myChild, $model);
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
    $this->addSelectCondition($param1, $param2, $param3);
    $projection = $this->getProjection();
    $this->getStatement()->setBasicSQL("SELECT $projection FROM {$this->table}");
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
      if (!is_null($myChild = $model->getMyChildren())) {
        if (isset($child)) $this->chooseMyChildConstraint($myChild, $model);
        $this->getDefaultChild($myChild, $model);
      }
      $models[] = $model;
    }

    $this->constraints = array();
    $this->conditions  = array();
    return $models;
  }

  public function addParent($row)
  {
    $this->parentTables = array($this->table);

    foreach ($row as $key => $val) {
      if (strpos($key, '_id') !== false) {
        $tblName   = str_replace('_id', '', $key);
        $modelName = array_map('ucfirst', explode('_', $tblName));
        $row[join('', $modelName)] = $this->addParentModels($tblName, $val);
      }
    }
    return $row;
  }

  protected function addParentModels($tblName, $id)
  {
    $tblName = strtolower($tblName);
    if ($this->structure !== 'tree' && $this->isAcquired($tblName)) return null;

    $model = $this->newClass($tblName);
    if (is_null($id)) return $model;

    if (!is_array($row = Sabel_DB_SimpleCache::get($tblName. $id))) {
      $model->setCondition($model->primaryKey, $id);
      $projection = $model->getProjection();
      $model->getStatement()->setBasicSQL("SELECT $projection FROM $tblName");
      $resultSet = $model->getExecuter()->execute();

      if (!$row = $resultSet->fetch()) {
        $model->selected = true;
        $model->id = $id;
        return $model;
      }
      Sabel_DB_SimpleCache::add($tblName. $id, $row);
    }

    foreach ($row as $key => $val) {
      if (strpos($key, '_id') !== false) {
        $tblName   = str_replace('_id', '', $key);
        $modelName = array_map('ucfirst', explode('_', $tblName));
        $row[join('', $modelName)] = $this->addParentModels($tblName, $val);
      } else {
        $row[$key] = $val;
      }
    }
    $this->mapping($model, $row);
    $model->unsetNewData();
    return $model;
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
    $projection = $class->getProjection();
    $class->getStatement()->setBasicSQL("SELECT $projection FROM {$class->table}");

    $this->chooseMyChildConstraint($child, $model);
    $model->setChildCondition("{$model->table}_id", $model->data[$model->primaryKey]);

    $class->conditions  = $model->childConditions;
    $class->constraints = $model->childConstraints[$child];

    $children = $model->getRecords($class, $child);
    if ($children) $model->data[$child] = $children;

    $this->childConditions  = array();
    $this->childConstraints = array();
    return $children;
  }

  protected function getDefaultChild($children, $model)
  {
    foreach (is_string($children) ? array($children) : $children as $val) {
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
      $constraints = $model->defChildConstraints;
    }

    if ($constraints) $model->setChildConstraint($child, $constraints);
    $model->defChildConstraints = $this->defChildConstraints;
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
    return ($this->modelExists($model)) ? new $model : new Sabel_DB_Basic($name);
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

    if ($this->is_selected()) {
      $this->conditions = $this->selectCondition;
      $this->getExecuter()->update($this->table, ($data) ? $data : $this->newData);
    } else {
      $data = ($data) ? $data : $this->data;
      foreach ($data as $key => $val) {
        if (is_object($val)) $data[$key] = $val->value;
      }
      return $this->getExecuter()->insert($this->table, $data, $this->checkIncColumn());
    }
  }

  public function allUpdate($data)
  {
    $this->getExecuter()->update($data);
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
    if (is_null($id) && !$this->is_selected())
      throw new Exception('Error: give the value of id or select the object beforehand.');

    $id = (isset($id)) ? $id : $this->data[$this->primaryKey];

    if (!class_exists('Schema_CascadeChain', false))
      throw new Exception('Error: class Schema_CascadeChain not exist.');

    $chain = Schema_CascadeChain::get();
    $key   = $this->connectName . ':' . $this->table;

    if (!array_key_exists($key, $chain)) {
      throw new Exception('cascade chain is not found. try remove()');
    } else {
      $this->begin();
      $models = array();
      foreach ($chain[$key] as $tblName) {
        $foreign = $this->table . '_id';
        if ($model = $this->pushStack($tblName, $foreign, $id)) $models[] = $model;
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
    $key   = $children[0]->getConnectName() . ':' . $table;

    if (array_key_exists($key, $chain)) {
      $references = array();
      foreach ($chain[$key] as $tblName) {
        $models = array();
        foreach ($children as $child) {
          $foreign = $this->table . '_id';
          if ($model = $this->pushStack($tblName, $foreign, $child->id)) $models[] = $model;
        }
        $references[] = $models;
      }
      unset($chain[$key]);

      foreach ($references as $models) {
        foreach ($models as $children) $this->getChainModels($children, $chain);
      }
    }
  }

  private function pushStack($chainValue, $foreign, $id)
  {
    list($cName, $tName) = explode(':', $chainValue);
    $model  = $this->newClass($tName);
    $model->setConnectName($cName);
    $models = $model->select($foreign, $id);

    if ($models) $this->cascadeStack["{$cName}:{$tName}:{$id}"] = $foreign;
    return $models;
  }

  private function clearCascadeStack($stack)
  {
    foreach ($stack as $param => $foreign) {
      list($cName, $tName, $idValue) = explode(':', $param);
      $model  = $this->newClass($tName);
      $model->setConnectName($cName);
      $model->remove($foreign, $idValue);
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
  public function ccond($key, $val)
  {
    $this->setChildCondition($key, $val);
  }
}
