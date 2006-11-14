<?php

/**
 * Sabel_DB_Relation
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Relation extends Sabel_DB_Executer
{
  private
    $joinPair     = array(),
    $joinColList  = array(),
    $joinColCache = array(),
    $joinConNames = array();

  private
    $parentTables    = array(),
    $relational      = array(),
    $acquiredParents = array(),
    $cascadeStack    = array();

  public function __construct($param1 = null, $param2 = null)
  {
    if (is_null($this->property)) $this->createProperty();
    if (!empty($param1)) $this->defaultSelectOne($param1, $param2);
  }

  private function createProperty()
  {
    $this->property = new Sabel_DB_Property(get_class($this), get_object_vars($this));
  }

  public function __set($key, $val)
  {
    $this->property->$key = $val;
  }

  public function __get($key)
  {
    return $this->property->$key;
  }

  public function __call($method, $parameters)
  {
    if (is_null($this->property)) $this->createProperty();
    @list($arg1, $arg2, $arg3) = $parameters;
    return $this->property->$method($arg1, $arg2, $arg3);
  }

  public function schema($tblName = null)
  {
    if (isset($tblName)) $this->getTableSchema($tblName)->getColumns();
    
    $columns = $this->getSchema()->getColumns();
    foreach ($this->getData() as $name => $value) {
      if (isset($columns[$name])) $columns[$name]->value = $this->convertData($name, $value);
    }
    
    return $columns;
  }

  /**
   * get rows count.
   *
   * @param  mixed    $param1 column name ( with the condition prefix ), or value of primary key.
   * @param  mixed    $param2 condition value.
   * @param  constant $param3 denial ( Sabel_DB_Condition::NOT )
   * @return integer rows count
   */
  public function getCount($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setCondition($param1, $param2, $param3);
    $this->setConstraint('limit', 1);

    $this->getStatement()->setBasicSQL('SELECT count(*) FROM ' . $this->table);
    $row = $this->exec()->fetch(Sabel_DB_Driver_ResultSet::NUM);
    return (int)$row[0];
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
      $tblName = $this->table;
      $columns = (is_null($group)) ? $this->primaryKey : $group;
      $model   = $this;
    } else {
      $tblName = convert_to_tablename($child);
      $columns = (is_null($group)) ? "{$this->table}_{$this->primaryKey}" : $group;
      $model   = $this->newClass($tblName);
      $model->constraints = $this->constraints;
    }
    $model->setConstraint('group', $columns);

    $model->getStatement()->setBasicSQL("SELECT $columns , $func FROM $tblName");
    return $model->toObject($model->exec());
  }

  protected function defaultSelectOne($param1, $param2 = null)
  {
    $this->setCondition($param1, $param2);
    $this->makeFindObject($this);
  }

  /**
   * retrieve one row from table.
   *
   * @param  mixed    $param1 column name ( with the condition prefix ), or value of primary key.
   * @param  mixed    $param2 condition value.
   * @param  constant $param3 denial ( Sabel_DB_Condition::NOT )
   * @return object
   */
  public function selectOne($param1 = null, $param2 = null, $param3 = null)
  {
    if (is_null($param1) && empty($this->conditions))
      throw new Exception('Error: selectOne() [WHERE] must be set condition.');

    $this->setCondition($param1, $param2, $param3);
    return $this->makeFindObject(clone($this));
  }

  private function makeFindObject($model)
  {
    $projection = $model->getProjection();
    $model->getStatement()->setBasicSQL("SELECT $projection FROM " . $model->table);

    if ($row = $model->exec()->fetch()) {
      $model->setData($model, ($model->isWithParent()) ? $this->addParent($row) : $row);
      if (!is_null($myChild = $model->getMyChildren())) $model->getDefaultChild($myChild, $model);
    } else {
      $model->receiveSelectCondition($model->conditions);
      foreach ($model->conditions as $condition) {
        $model->{$condition->key} = $condition->value;
      }
    }
    return $model;
  }

  /**
   * retrieve rows from table by join query of some types.
   *
   * @param  array  $modelPairs model pairs. (ex. 'Hoge:Huga'
   * @param  string $joinType   'INNER'( default ) or 'LEFT' or 'RIGHT'
   * @param  array  $colList    key is model name. and set the columns name in it.
   * @return array
   */
  public function selectJoin($modelPairs, $joinType = 'INNER', $colList = null)
  {
    if (!is_array($modelPairs))
      throw new Exception('Error: joinSelect() argument must be an array.');

    $sql        = array('SELECT ');
    $joinTables = array();
    $myTable    = $this->table;
    $relTables  = $this->toTablePair($modelPairs);
    $colList    = $this->remakeColList($colList);

    $columns = (isset($colList[$myTable])) ? $colList[$myTable] : $this->getColumnNames();
    foreach ($columns as $column) $sql[] = "{$myTable}.{$column}, ";

    foreach ($relTables as $pair) $joinTables = array_merge($joinTables, array_values($pair));
    $joinTables = array_diff(array_unique($joinTables), (array)$myTable);

    foreach ($joinTables as $tblName) $this->addJoinColumns($sql, $tblName, $colList);

    $sql = array(substr(join('', $sql), 0, -2));
    $sql[] = " FROM {$myTable}";

    foreach ($relTables as $pair) {
      list($child, $parent) = array_values($pair);
      $sql[] = " $joinType JOIN $parent ON {$child}.{$parent}_id = {$parent}.id ";
    }

    $this->getStatement()->setBasicSQL(join('', $sql));
    $resultSet = $this->exec();
    if ($resultSet->isEmpty()) return false;

    $results = array();
    foreach ($resultSet as $row) {
      list($self, $models) = $this->makeEachModels($row, $joinTables);
      $relational = $this->relational;

      foreach ($joinTables as $tblName) {
        if (!isset($relational[$tblName])) continue;
        foreach ($relational[$tblName] as $parent) {
          $mdlName = convert_to_modelname($parent);
          $models[$tblName]->dataSet($mdlName, $models[$parent]);
        }
      }

      foreach ($relational[$myTable] as $parent) {
        $mdlName = convert_to_modelname($parent);
        $self->dataSet($mdlName, $models[$parent]);
        $self->$mdlName = $models[$parent];
      }
      $results[] = $self;
    }
    return $results;
  }

  private function toTablePair($modelPairs)
  {
    $relTables = array();

    foreach ($modelPairs as $pair) {
      list($child, $parent) = array_map('convert_to_tablename', explode(':', $pair));
      $this->relational[$child][] = $parent;
      $relTables[] = array($child, $parent);
    }
    return $relTables;
  }

  private function remakeColList($colList)
  {
    if (empty($colList)) return array();

    foreach ($colList as $key => $colNames) {
      $newKey = convert_to_tablename($key);
      $colList[$newKey] = $colNames;
      unset($colList[$key]);
    }
    return $colList;
  }

  private function addJoinColumns(&$sql, $tblName, $colList = null)
  {
    $columns = (isset($colList[$tblName])) ? $colList[$tblName] : $this->getColumnNames($tblName);
    foreach ($columns as $column) {
      $this->joinColCache[$tblName][] = $column;
      $sql[] = "{$tblName}.{$column} AS pre_{$tblName}_{$column}, ";
    }
  }

  private function makeEachModels($row, $joinTables)
  {
    $models   = array();
    $acquire  = array();
    $colCache = $this->joinColCache;

    foreach ($joinTables as $tblName) {
      $model  = $this->newClass($tblName);
      $pKey   = $model->primaryKey;
      $preCol = "pre_{$tblName}_{$pKey}";
      $cache  = Sabel_DB_SimpleCache::get($tblName . $row[$preCol]);

      if (is_object($cache)) {
        $models[$tblName] = clone($cache);
      } else {
        foreach ($colCache[$tblName] as $column) {
          $preCol = "pre_{$tblName}_{$column}";
          $acquire[$tblName][$column] = $row[$preCol];
          unset($row[$preCol]);
        }
        $this->setData($model, $acquire[$tblName]);
        $models[$tblName] = $model;
        Sabel_DB_SimpleCache::add($tblName . $model->$pKey, $model);
      }
    }

    $model = $this->newClass($this->table);
    $this->setData($model, $row);
    $models[$this->table] = $model;
    return array($model, $models);
  }

  private function prepareAutoJoin($tblName)
  {
    if (!$sClass = get_schema_by_tablename($tblName)) return false;
    if (!$this->isSameConnectName($sClass->getProperty())) return false;

    $this->joinColList[$tblName] = array_keys($sClass->get());
    if ($parents = $sClass->getParents()) {
      foreach ($parents as $parent) {
        if (in_array($parent, $this->acquiredParents)) continue;
        $this->joinPair[] = $tblName . ':' . $parent;
        $this->acquiredParents[] = $parent;
        if (!$this->prepareAutoJoin($parent)) return false;
      }
    }
    return true;
  }

  private function isSameConnectName($props)
  {
    $conName = $props['connectName'];
    if (($size = sizeof($this->joinConNames)) > 0) {
      if ($this->joinConNames[$size - 1] !== $conName) return false;
    }
    $this->joinConNames[] = $conName;
    return true;
  }

  /**
   * retrieve rows from table.
   *
   * @param  mixed    $param1 column name ( with the condition prefix ), or value of primary key.
   * @param  mixed    $param2 condition value.
   * @param  constant $param3 denial ( Sabel_DB_Condition::NOT )
   * @return array
   */
  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setCondition($param1, $param2, $param3);
    if ($this->isWithParent() && $this->prepareAutoJoin($this->table)) {
      return $this->selectJoin($this->joinPair, 'LEFT', $this->joinColList);
    }

    $projection = $this->getProjection();
    $this->getStatement()->setBasicSQL("SELECT $projection FROM {$this->table}");
    return $this->getRecords($this);
  }

  private function getRecords($model, $child = null)
  {
    $resultSet = $model->exec();
    if ($resultSet->isEmpty()) return false;

    $models = array();
    foreach ($resultSet as $row) {
      if (is_null($child)) {
        $model = $this->newClass($this->table);
        $withParent = $this->isWithParent();

        $cconst = $this->getChildConstraint();
        if ($cconst) $model->receiveChildConstraint($cconst);
      } else {
        $model = $this->newClass($child);
        $withParent = ($this->isWithParent()) ? true : $model->isWithParent();
      }

      $this->setData($model, ($withParent) ? $this->addParent($row) : $row);
      if ($myChild = $model->getMyChildren()) {
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
    return $this->checkForeignKey($row, $this->primaryKey);
  }

  private function addParentModels($tblName, $id)
  {
    $tblName = strtolower($tblName);
    if ($this->getStructure() !== 'tree' && $this->isAcquired($tblName)) return false;

    $model = $this->newClass($tblName);
    if (is_null($id)) return $model;

    if (!is_array($row = Sabel_DB_SimpleCache::get($tblName . $id))) {
      $model->setCondition($model->primaryKey, $id);
      $projection = $model->getProjection();
      $model->getStatement()->setBasicSQL("SELECT $projection FROM $tblName");
      $resultSet  = $model->exec();

      if (!$row = $resultSet->fetch()) {
        throw new Exception('Error: relational error. parent does not exists.');
      }

      Sabel_DB_SimpleCache::add($tblName . $id, $row);
    }

    $row = $this->checkForeignKey($row, $model->primaryKey);
    $this->setData($model, $row);
    return $model;
  }

  private function checkForeignKey($row, $pKey)
  {
    foreach ($row as $key => $val) {
      if (strpos($key, "_{$pKey}") !== false) {
        $tblName = str_replace("_{$pKey}", '', $key);
        $result  = $this->addParentModels($tblName, $val);
        if ($result) {
          $mdlName = convert_to_modelname($tblName);
          $row[$mdlName] = $result;
        }
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

  /**
   * fetch the children by relating own primary key to foreign key of a given table name.
   *   strongly recommend 'id' for a primary key.
   *   strongly recommend parent table name + '_id' for a foreign key.
   *
   * @param  string $child model name.
   * @param  mixed  $model need not be used. ( used internally )
   * @return array
   */
  public function getChild($child, $model = null)
  {
    if (is_null($model)) $model = $this;

    $cModel = $this->newClass($child);
    $projection = $cModel->getProjection();
    $cModel->getStatement()->setBasicSQL("SELECT $projection FROM {$cModel->table}");

    $this->chooseChildConstraint($child, $model);
    $primary = $model->primaryKey;
    $model->setChildCondition("{$model->table}_{$primary}", $model->$primary);

    $cModel->conditions = $model->getChildCondition();
    $cconst = $model->getChildConstraint();
    if (isset($cconst[$child])) $cModel->constraints = $cconst[$child];

    $children = $model->getRecords($cModel, $child);
    if ($children) $model->dataSet($child, $children);
    return $children;
  }

  private function getDefaultChild($children, $model)
  {
    $children = (is_string($children)) ? (array)$children : $children;

    foreach ($children as $val) {
      $this->chooseChildConstraint($val, $model);
      $model->getChild($val, $model);
    }
  }

  private function chooseChildConstraint($child, $model)
  {
    $thisDefault = $this->getDefChildConstraint();
    $thisCConst  = $this->getChildConstraint();
    $modelCConst = $model->getChildConstraint();

    if (isset($thisCConst[$child])) {
      $constraints = $thisCConst[$child];
    } elseif ($thisDefault) {
      $constraints = $thisDefault;
    } elseif (isset($modelCConst[$child])) {
      $constraints = $modelCConst[$child];
    } else {
      $constraints = $model->getDefChildConstraint();
    }
    if ($constraints) $model->setChildConstraint($child, $constraints);
    if ($thisDefault) $model->setDefChildConstraint($thisDefault);
  }

  protected function setData($model, $row)
  {
    $primaryKey = $model->primaryKey;

    if (is_array($primaryKey)) {
      foreach ($primaryKey as $key) {
        $condition = new Sabel_DB_Condition($key, $row[$key]);
        $model->setSelectCondition($key, $condition);
      }
    } else {
      if (isset($row[$primaryKey])) {
        $condition = new Sabel_DB_Condition($primaryKey, $row[$primaryKey]);
        $model->setSelectCondition($primaryKey, $condition);
      }
    }

    $model->setProperties($row);
    $model->enableSelected();
  }

  public function newChild($child = null)
  {
    $data = $this->getData();
    $id   = $data[$this->primaryKey];

    if (empty($id)) {
      throw new Exception("Sabel_DB_Relation::newChild() who is a parent? hasn't id value.");
    }

    $parent  = $this->table;
    $tblName = (is_null($child)) ? $parant : $child;
    $model   = $this->newClass($tblName);
    $column  = "{$parent}_{$this->primaryKey}";
    $model->$column = $id;
    return $model;
  }

  protected function newClass($name)
  {
    $mdlName = convert_to_modelname($name);
    return ($this->modelExists($mdlName)) ? new $mdlName : Sabel_DB_Model::load($mdlName);
  }

  private function modelExists($className)
  {
    return (class_exists($className, false) && strtolower($className) !== 'sabel_db_empty');
  }

  public function clearChild($child)
  {
    $pkey = $this->primaryKey;
    $data = $this->getData();

    if (isset($data[$pkey])) {
      $id = $data[$pkey];
    } else {
      throw new Exception("Sabel_DB_Relation::clearChild() who is a parent? hasn't id value.");
    }

    $model = $this->newClass($child);

    $model->setCondition("{$this->table}_{$pkey}", $id);
    $model->getStatement()->setBasicSQL('DELETE FROM ' . $model->table);
    $model->exec();
  }

  public function save($data = null)
  {
    if (!empty($data) && !is_array($data))
      throw new Exception('Sabel_DB_Relation::save() argument must be an array');

    if ($this->isSelected()) {
      $saveData = ($data) ? $data : $this->getNewData();
      $this->conditions = $this->getSelectCondition();
      $this->update($this->table, $saveData);
      $this->unsetNewData();
    } else {
      $saveData = ($data) ? $data : $this->getData();
      if ($incCol = $this->checkIncColumn()) {
        $newId = $this->insert($this->table, $saveData, $incCol);
        $this->dataSet($incCol, $newId);
      } else {
        $this->insert($this->table, $saveData, false);
      }
    }

    foreach ($saveData as $key => $val) $this->dataSet($key, $val);
    return $this;
  }

  public function allUpdate($data)
  {
    $this->update($this->table, $data);
  }

  public function multipleInsert($data)
  {
    if (!is_array($data)) {
      throw new Exception('Sabel_DB_Relation::multipleInsert() data is not array.');
    }

    Sabel_DB_Transaction::add($this);

    try {
      $this->ArrayInsert($this->table, $data, $this->checkIncColumn());
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }

    Sabel_DB_Transaction::commit();
  }

  /**
   * remove row(s) from the table.
   *
   * @param  mixed     $param1 column name ( with the condition prefix ), or value of primary key.
   * @param  mixed     $param2 condition value.
   * @param  constrant $param3 denial ( Sabel_DB_Condition::NOT )
   * @return void
   */
  public function remove($param1 = null, $param2 = null, $param3 = null)
  {
    $idValue = null;

    $selectConditions = $this->getSelectCondition();
    if (isset($selectConditions[$this->primaryKey])) {
      $idValue = $selectConditions[$this->primaryKey]->value;
    }

    if (is_null($param1) && empty($this->conditions) && is_null($idValue)) {
      throw new Exception("Sabel_DB_Relation::remove() must be set condition");
    }

    if (isset($param1)) {
      $this->setCondition($param1, $param2, $param3);
    } elseif (isset($idValue)) {
      $this->setCondition($this->primaryKey, $idValue);
    }

    $this->getStatement()->setBasicSQL('DELETE FROM ' . $this->table);
    $this->exec();
  }

  /**
   * cascade delete.
   *
   * @param  integer $id value of id ( primary key ).
   * @return void
   */
  public function cascadeDelete($id = null)
  {
    if (!class_exists('Schema_CascadeChain', false))
      throw new Exception('Error: class Schema_CascadeChain does not exist.');

    if (is_null($id) && !$this->isSelected())
      throw new Exception('Error: give the value of id or select the model beforehand.');

    $data  = $this->getData();
    $id    = (isset($id)) ? $id : $data[$this->primaryKey];
    $chain = Schema_CascadeChain::get();
    $key   = $this->connectName . ':' . $this->table;

    if (!isset($chain[$key])) {
      throw new Exception("Sabel_DB_Relation::cascadeDelete() $key is not found. try remove()");
    }

    Sabel_DB_Transaction::add($this);

    $models = array();
    $table  = $this->table;
    $pkey   = $this->primaryKey;
    foreach ($chain[$key] as $tblName) {
      $foreignKey = "{$table}_{$pkey}";
      if ($model = $this->pushStack($tblName, $foreignKey, $id)) $models[] = $model;
    }

    foreach ($models as $children) $this->makeChainModels($children, $chain);

    $this->clearCascadeStack(array_reverse($this->cascadeStack));
    $this->remove($this->primaryKey, $id);

    Sabel_DB_Transaction::commit();
  }

  private function makeChainModels($children, &$chain)
  {
    $key = $children[0]->connectName . ':' . $children[0]->table;
    if (!isset($chain[$key])) return null;

    foreach ($chain[$key] as $tblName) {
      $models = array();
      foreach ($children as $child) {
        $foreignKey = "{$child->table}_{$child->primaryKey}";
        if ($model = $this->pushStack($tblName, $foreignKey, $child->id)) $models[] = $model;
      }
      if ($models) {
        foreach ($models as $children) $this->makeChainModels($children, $chain);
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

  /**
   * execute a query directly.
   *
   * @param  string $sql   execute query.
   * @param  array  $param character strings where it should escape.
   * @return array
   */
  public function execute($sql, $param = null)
  {
    if (!empty($param) && !is_array($param))
      throw new Exception('Error: execute() second argument must be an array');

    return $this->toObject($this->executeQuery($sql, $param));
  }

  protected function toObject($resultSet)
  {
    if ($resultSet->isEmpty()) return false;

    $models  = array();
    foreach ($resultSet as $row) {
      $cloned = $model = $this->newClass($this->table);
      $cloned->setProperties($row);
      $models[] = $cloned;
    }
    return $models;
  }
}
