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

  protected $executer = null;

  protected
    $conditions      = array(),
    $selectCondition = array();

  protected
    $constraints         = array(),
    $childConditions     = array(),
    $childConstraints    = array(),
    $defChildConstraints = array();

  protected
    $data    = array(),
    $newData = array();

  protected 
    $joinColCache = array(),
    $selfParents  = array();

  protected
    $cachedParent = array(),
    $cascadeStack = array();

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

  public function getTableSchema()
  {
    $sa = new Sabel_DB_Schema_Accessor($this->connectName, $this->getSchemaName());
    return $sa->getTable($this->table);
  }

  public function getAllSchema()
  {
    $sa = new Sabel_DB_Schema_Accessor($this->connectName, $this->getSchemaName());
    return $sa->getTables();
  }

  public function setConstraint($param1, $param2 = null)
  {
    foreach ((is_array($param1)) ? $param1 : array($param1 => $param2) as $key => $val) {
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
      $param1 = $this->primary;
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
    $this->conditions = array();
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

  public function getColumnNames($table = null)
  {
    $table = (isset($table)) ? $table : $this->table;

    $this->setConstraint('limit', 1);
    $this->getStatement()->setBasicSQL("SELECT * FROM {$table}");
    $resultSet = $this->getExecuter()->execute();
    return array_keys($resultSet->fetch());
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
      $columns = (is_null($group)) ? $this->incColumn : $group;
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

    $this->getStatement($model)->setBasicSQL("SELECT {$model->projection} FROM " . $model->table);
    $resultSet = $this->getExecuter($model)->execute();

    if ($row = $resultSet->fetch()) {
      if ($model->withParent) {
        $relation = new Sabel_DB_Relation();
        $row = $relation->addParent($row, $model->table, $model->structure);
      }
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

    $sql        = array('SELECT ');
    $joinTables = array();
    $myTable    = $this->table;
    $relTables  = $this->toArrayJoinTables($relTableList);

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

    $recordObj = array();
    foreach ($resultSet as $row) {
      list($self, $models) = $this->makeEachModelObject($row, $relTables);
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
      $recordObj[] = $self;
    }
    return $recordObj;
  }

  protected $relational = array();

  private function toArrayJoinTables($relTableList)
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

  private function makeEachModelObject($row, $relTableArray)
  {
    $models   = array();
    $acquire  = array();
    $colCache = $this->joinColCache;

    foreach ($relTableArray as $pair) {
      foreach ($pair as $table) {
        if ($table !== $this->table && !isset($acquire[$table])) {
          foreach ($colCache[$table] as $column) {
            $preCol = "pre_{$table}_{$column}";
            $acquire[$table][$column] = $row[$preCol];
            unset($row[$preCol]);
          }
          $model = $this->newClass($table);
          $this->setSelectedProperty($model, $acquire[$table]);
          $models[$table] = $model;
        }
      }
    }

    $model = $this->newClass($this->table);
    $this->setSelectedProperty($model, $row);
    $models[$this->table] = $model;
    return array($model, $models);
  }

  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    $this->addSelectCondition($param1, $param2, $param3);
    $this->getStatement()->setBasicSQL("SELECT {$this->projection} FROM {$this->table}");
    return $this->getRecords($this);
  }

  protected function getRecords($model, $child = null)
  {
    $resultSet = $this->getExecuter($model)->execute();
    if ($resultSet->isEmpty()) return false;

    $recordObj = array();
    foreach ($resultSet as $row) {
      if (is_null($child)) {
        $model = $this->newClass($this->table);
        $withParent = $this->withParent;
        if ($this->childConstraints) $model->childConstraints = $this->childConstraints;
      } else {
        $model = $this->newClass($child);
        $withParent = ($this->withParent) ? true : $model->withParent;
      }

      if ($withParent) {
        $relation = new Sabel_DB_Relation();
        $row = $relation->addParent($row, $model->table, $model->structure);
      }
      $this->setSelectedProperty($model, $row);

      if (!is_null($myChild = $model->getMyChildren())) {
        if (isset($child)) $this->chooseMyChildConstraint($myChild, $model);
        $this->getDefaultChild($myChild, $model);
      }
      $recordObj[] = $model;
    }

    $this->constraints = array();
    $this->conditions  = array();
    return $recordObj;
  }

  public function getChild($child, $model = null)
  {
    if (is_null($model)) $model = $this;

    $class = $this->newClass($child);
    $this->getStatement($class)->setBasicSQL("SELECT {$class->projection} FROM {$class->table}");

    $this->chooseMyChildConstraint($child, $model);
    $model->setChildCondition("{$model->table}_id", $model->data[$model->incColumn]);

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

  protected function setSelectedProperty($model, $row)
  {
    $primary = $model->primary;

    if (empty($model->jointKey)) {
      if (isset($row[$primary])) {
        $condition = new Sabel_DB_Condition($primary, $row[$primary]);
        $model->selectCondition[$primary] = $condition;
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
    $id = $this->data[$this->incColumn];
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
    return ($this->mapperClassExists($model)) ? new $model : new Sabel_DB_Basic($model);
  }

  private function mapperClassExists($className)
  {
    return (class_exists($className, false) && strtolower($className) !== 'sabel_db_basic');
  }

  public function clearChild($child)
  {
    if (isset($this->data[$this->incColumn])) {
      $id = $this->data[$this->incColumn];
    } else {
      throw new Exception('Error: clearChild() who is a parent? hasn\'t id value.');
    }
    $model = $this->newClass($child);

    $model->setCondition($this->table . '_id', $id);
    $this->getStatement($model)->setBasicSQL('DELETE FROM ' . $model->table);
    $this->getExecuter($model)->execute();
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
    return ($this->isAutoNumber()) ? $this->incColumn : false;
  }

  public function remove($param1 = null, $param2 = null, $param3 = null)
  {
    $idValue = null;

    if (isset($this->selectCondition[$this->incColumn]))
      $idValue = $this->selectCondition[$this->incColumn]->value;

    if (is_null($param1) && empty($this->conditions) && is_null($idValue))
      throw new Exception("Error: remove() [WHERE] must be set condition");

    if (isset($param1)) {
      $this->setCondition($param1, $param2, $param3);
    } else if (isset($idValue)) {
      $this->setCondition($this->incColumn, $idValue);
    }

    $this->getStatement()->setBasicSQL('DELETE FROM ' . $this->table);
    $this->getExecuter()->execute();
  }

  public function cascadeDelete($id = null)
  {
    if (is_null($id) && !$this->is_selected())
      throw new Exception('Error: give the value of id or select the object beforehand.');

    $id = (isset($id)) ? $id : $this->data[$this->incColumn];

    $chain = Cascade_Chain::get();
    $myKey = $this->connectName . ':' . $this->table;

    if (!array_key_exists($myKey, $chain)) {
      throw new Exception('cascade chain is not found. try remove()');
    } else {
      $this->begin();
      $models = array();
      foreach ($chain[$myKey] as $chainModel) {
        if ($model = $this->pushCascadeStack($chainModel, "{$this->table}_id", $id)) $models[] = $model;
      }

      foreach ($models as $children) $this->getChainModels($children, $chain);

      $this->clearCascadeStack(array_reverse($this->cascadeStack));
      $this->remove($this->incColumn, $id);
      $this->commit();
    }
  }

  protected function getChainModels($children, &$chain)
  {
    $table    = $children[0]->getTableName();
    $chainKey = $children[0]->getConnectName() . ':' . $table;

    if (array_key_exists($chainKey, $chain)) {
      $references = array();
      foreach ($chain[$chainKey] as $chainModel) {
        $models = array();
        foreach ($children as $child) {
          if ($model = $this->pushCascadeStack($chainModel, "{$table}_id", $child->id)) $models[] = $model;
        }
        $references[] = $models;
      }
      unset($chain[$chainKey]);

      foreach ($references as $models) {
        foreach ($models as $children) $this->getChainModels($children, $chain);
      }
    }
  }

  private function pushCascadeStack($chainValue, $foreign, $id)
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

    $recordObj = array();
    $model = $this->newClass($this->table);
    foreach ($resultSet as $row) {
      $cloned = clone($model);
      $cloned->setProperties($row);
      $recordObj[] = $cloned;
    }
    return $recordObj;
  }

  protected function getExecuter($model = null)
  {
    $model = (is_null($model)) ? $this : $model;
    if (isset($model->executer)) return $model->executer;

    $model->executer = new Sabel_DB_Executer($model);
    return $model->executer;
  }

  protected function getStatement($model = null)
  {
    $model = (is_null($model)) ? $this : $model;
    return $model->getExecuter($model)->getDriver()->getStatement();
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

  protected $connectName = 'default';

  public function setConnectName($connectName)
  {
    $this->connectName = $connectName;
  }

  public function getConnectName()
  {
    return $this->connectName;
  }

  public function setProperties($array)
  {
    if (!is_array($array)) throw new Exception('properties Argument is not array.');
    foreach ($array as $key => $val) $this->$key = $val;
  }

  protected $primary = 'id';

  public function setPrimaryKey($key)
  {
    $this->primary = $key;
  }

  public function getPrimaryKey()
  {
    return $this->primary;
  }

  protected $jointKey = array();

  public function setJointKey($keys)
  {
    if (!is_array($keys)) throw new Exception('joint keys are not array.');
    $this->jointKey = $keys;
  }

  public function getJointKey()
  {
    return $this->jointKey;
  }

  protected $incColumn = 'id';

  public function setIncColumn($key)
  {
    $this->incColumn = $key;
  }

  public function getIncColumn()
  {
    return $this->incColumn;
  }

  protected $autoNumber = true;

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

  protected $table = '';

  public function setTableName($table)
  {
    $this->table = $table;
  }

  public function getTableName()
  {
    return $this->table;
  }

  protected $projection = '*';

  public function setProjection($p)
  {
    $this->projection = (is_array($p)) ? implode(',', $p) : $p;
  }

  public function getProjection()
  {
    return $this->projection;
  }

  protected $structure = 'normal';

  public function setStructure($structure)
  {
    $this->structure = $structure;
  }

  public function getStructure()
  {
    return $this->structure;
  }

  protected $myChildren = null;

  public function setMyChildren($children)
  {
    $this->myChildren = $children;
  }

  public function getMyChildren()
  {
    return $this->myChildren;
  }

  protected $withParent = false;

  public function enableParent()
  {
    $this->withParent = true;
  }

  public function disableParent()
  {
    $this->withParent = false;
  }

  protected $selected = false;

  public function enableSelected()
  {
    $this->selected = true;
  }

  public function disableSelected()
  {
    $this->selected = false;
  }

  public function unsetNewData()
  {
    $this->newData = array();
  }
}
