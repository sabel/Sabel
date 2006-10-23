<?php

/**
 * Sabel_DB_Wrapper
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Wrapper
{
  private
    $property = null;

  private
    $joinPair     = array(),
    $joinColList  = array(),
    $joinColCache = array();

  private
    $parentTables = array(),
    $relational   = array(),
    $cascadeStack = array();

  public function __construct($param1 = null, $param2 = null)
  {
    if (is_null($this->property)) $this->createProperty();
    if (Sabel_DB_Transaction::isActive()) $this->begin();
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
    if (isset($tblName)) {
      return $this->getTableSchema($tblName)->getColumns();
    } else {
      $columns = $this->getTableSchema()->getColumns();
      foreach ($this->getData() as $name => $data) {
        if (isset($columns[$name])) $columns[$name]->value = $data;
      }
      return $columns;
    }
  }

  public function getTableNames()
  {
    return $this->createSchemaAccessor()->getTableNames();
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

  private function createSchemaAccessor()
  {
    $connectName = $this->connectName;
    $schemaName  = Sabel_DB_Connection::getSchema($connectName);
    return new Sabel_DB_Schema_Accessor($connectName, $schemaName);
  }

  public function begin()
  {
    $driver = $this->getExecuter()->getDriver();
    $check  = true;

    if (Sabel_DB_Connection::getDB($this->connectName) === 'mysql') {
      $check = $this->checkTableEngine($driver);
    }

    if ($check) Sabel_DB_Transaction::begin($this->connectName, $driver);
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

    $executer = $this->getExecuter();
    $executer->getStatement()->setBasicSQL('SELECT count(*) FROM ' . $this->table);
    $row = $executer->execute()->fetch(Sabel_DB_Driver_ResultSet::NUM);
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
      $table   = $this->table;
      $columns = (is_null($group)) ? $this->primaryKey : $group;
    } else {
      $table   = $child;
      $columns = (is_null($group)) ? "{$this->table}_{$this->primaryKey}" : $group;
    }
    $this->setConstraint('group', $columns);

    $executer = $this->getExecuter();
    $executer->getStatement()->setBasicSQL("SELECT $columns , $func FROM $table");
    return $this->toObject($executer->execute());
  }

  protected function defaultSelectOne($param1, $param2 = null)
  {
    $this->setCondition($param1, $param2);
    $this->makeFindObject($this);
  }

  public function selectOne($param1 = null, $param2 = null, $param3 = null)
  {
    $conditions = $this->getCondition();
    if (is_null($param1) && empty($conditions))
      throw new Exception('Error: selectOne() [WHERE] must be set condition.');

    $this->setCondition($param1, $param2, $param3);
    return $this->makeFindObject(clone($this));
  }

  private function makeFindObject($model)
  {
    $model->receiveSelectCondition($model->getCondition());
    $projection = $model->getProjection();
    $executer   = $model->getExecuter();
    $executer->getStatement()->setBasicSQL("SELECT $projection FROM " . $model->table);

    if ($row = $executer->execute()->fetch()) {
      $model->setData($model, ($model->isWithParent()) ? $this->addParent($row) : $row);
      if (!is_null($myChild = $model->getMyChildren())) $model->getDefaultChild($myChild, $model);
    } else {
      foreach ($model->getCondition() as $condition) {
        $key = $condition->key;
        $model->$key = $condition->value;
      }
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

    $executer = $this->getExecuter();
    $executer->getStatement()->setBasicSQL(join('', $sql));
    $resultSet = $executer->execute();
    if ($resultSet->isEmpty()) return false;

    $results = array();
    foreach ($resultSet as $row) {
      list($self, $models) = $this->makeEachModels($row, $joinTables);
      $relational = $this->relational;

      foreach ($joinTables as $table) {
        if (!array_key_exists($table, $relational)) continue;
        foreach ($relational[$table] as $parent) {
          $mdlName = join('', array_map('ucfirst', explode('_', $parent)));
          $models[$table]->$mdlName = $models[$parent];
          $models[$table]->unsetNewData();
        }
      }

      foreach ($relational[$myTable] as $parent) {
        $mdlName = join('', array_map('ucfirst', explode('_', $parent)));
        $self->$mdlName = $models[$parent];
      }
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
      $model  = $this->newClass($table);
      $pKey   = $model->primaryKey;
      $preCol = "pre_{$table}_{$pKey}";
      $cache  = Sabel_DB_SimpleCache::get($table . $row[$preCol]);

      if (is_object($cache)) {
        $models[$table] = clone($cache);
      } else {
        foreach ($colCache[$table] as $column) {
          $preCol = "pre_{$table}_{$column}";
          $acquire[$table][$column] = $row[$preCol];
          unset($row[$preCol]);
        }
        $this->setData($model, $acquire[$table]);
        $models[$table] = $model;
        Sabel_DB_SimpleCache::add($table . $model->$pKey, $model);
      }
    }

    $model = $this->newClass($this->table);
    $this->setData($model, $row);
    $models[$this->table] = $model;
    return array($model, $models);
  }

  private function prepareAutoJoin($tblName)
  {
    $scName = 'Schema_' . str_replace('_', '', $tblName);
    $sClass = (class_exists($scName, false)) ? new $scName() : false;
    if (!$sClass) return false;

    $this->joinColList[$tblName] = array_keys($sClass->get());

    if ($parents = $sClass->getParents()) {
      foreach ($parents as $parent) {
        $this->joinPair[] = $tblName . ':' . $parent;
        return $this->prepareAutoJoin($parent);
      }
    }
    return true;
  }

  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    if ($this->isWithParent() && $this->prepareAutoJoin($this->table)) {
      return $this->selectJoin($this->joinPair, $this->joinColList);
    }

    $this->setCondition($param1, $param2, $param3);
    $projection = $this->getProjection();
    $executer   = $this->getExecuter();
    $executer->getStatement()->setBasicSQL("SELECT $projection FROM {$this->table}");
    return $this->getRecords($executer);
  }

  private function getRecords($executer, $child = null)
  {
    $resultSet = $executer->execute();
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
      if (!is_null($myChild = $model->getMyChildren())) {
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

    if (!is_array($row = Sabel_DB_SimpleCache::get($tblName. $id))) {
      $model->setCondition($model->primaryKey, $id);
      $projection = $model->getProjection();
      $executer   = $model->getExecuter();
      $executer->getStatement()->setBasicSQL("SELECT $projection FROM $tblName");
      $resultSet = $executer->execute();

      if (!$row = $resultSet->fetch())
        throw new Exception('Error: relational error. parent does not exists.');

      Sabel_DB_SimpleCache::add($tblName. $id, $row);
    }
    $row = $this->checkForeignKey($row, $model->primaryKey);

    $this->setData($model, $row);
    $model->unsetNewData();
    return $model;
  }

  private function checkForeignKey($row, $pKey)
  {
    foreach ($row as $key => $val) {
      if (strpos($key, "_{$pKey}") !== false) {
        $tblName = str_replace("_{$pKey}", '', $key);
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
    $projection = $class->getProjection();
    $executer   = $class->getExecuter();
    $executer->getStatement()->setBasicSQL("SELECT $projection FROM {$class->table}");

    $this->chooseChildConstraint($child, $model);
    $primary = $model->primaryKey;
    $model->setChildCondition("{$model->table}_{$primary}", $model->$primary);

    $class->receiveCondition($model->getChildCondition());
    $cconst = $model->getChildConstraint();
    $class->receiveConstraint($cconst[$child]);

    $children = $model->getRecords($executer, $child);
    if ($children) $model->$child = $children;
    return $children;
  }

  private function getDefaultChild($children, $model)
  {
    foreach (is_string($children) ? (array)$children : $children as $val) {
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
    } else if ($thisDefault) {
      $constraints = $thisDefault;
    } else if (isset($modelCConst[$child])) {
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
    if (empty($id)) throw new Exception('Error: newChild() who is a parent? hasn\'t id value.');

    $parent = $this->table;
    $table  = (is_null($child)) ? $parant : $child;
    $model  = $this->newClass($table);

    $column = "{$parent}_{$this->primaryKey}";
    $model->$column = $id;
    return $model;
  }

  protected function newClass($name)
  {
    $model = str_replace('_', '', $name);

    if ($this->modelExists($model)) {
      return new $model();
    } else {
      $tblName = substr(strtolower(preg_replace('/([A-Z])/', '_$1', $name)), 1);
      $class = new Sabel_DB_Basic($tblName);
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
    $data = $this->getData();
    if (isset($data[$this->primaryKey])) {
      $id = $data[$this->primaryKey];
    } else {
      throw new Exception('Error: clearChild() who is a parent? hasn\'t id value.');
    }
    $model = $this->newClass($child);

    $model->setCondition("{$this->table}_{$this->primaryKey}", $id);
    $executer = $model->getExecuter();
    $executer->getStatement()->setBasicSQL('DELETE FROM ' . $model->table);
    $executer->execute();
  }

  public function save($data = null)
  {
    if (!empty($data) && !is_array($data))
      throw new Exception('Error: save() argument must be an array');

    if ($this->isSelected()) {
      $this->receiveCondition($this->getSelectCondition());
      $this->getExecuter()->update($this->table, ($data) ? $data : $this->getNewData());
    } else {
      $data = ($data) ? $data : $this->getData();
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
  
  private function checkIncColumn()
  {
    return ($this->isAutoNumber()) ? $this->incrementKey : false;
  }
  
  /**
   * remove object
   *
   * @param mixed $param1 hereisdescription
   * @param mixed $param2 this is test
   * @param mixed $param3 this is test
   * @return void
   */
  public function remove($param1 = null, $param2 = null, $param3 = null)
  {
    $idValue = null;

    $selectConditions = $this->getSelectCondition();
    if (isset($selectConditions[$this->primaryKey])) {
      $idValue = $selectConditions[$this->primaryKey]->value;
    }

    $conditions = $this->getCondition();
    if (is_null($param1) && empty($conditions) && is_null($idValue)) {
      throw new Exception("Error: remove() [WHERE] must be set condition");
    }

    if (isset($param1)) {
      $this->setCondition($param1, $param2, $param3);
    } else if (isset($idValue)) {
      $this->setCondition($this->primaryKey, $idValue);
    }

    $executer = $this->getExecuter();
    $executer->getStatement()->setBasicSQL('DELETE FROM ' . $this->table);
    $executer->execute();
  }

  public function cascadeDelete($id = null)
  {
    if (is_null($id) && !$this->isSelected()) {
      throw new Exception('Error: give the value of id or select the object beforehand.');
    }

    $data = $this->getData();
    $id   = (isset($id)) ? $id : $data[$this->primaryKey];

    if (!class_exists('Schema_CascadeChain', false)) {
      throw new Exception('Error: class Schema_CascadeChain does not exist.');
    }

    $chain = Schema_CascadeChain::get();
    $key   = $this->connectName . ':' . $this->table;

    if (!array_key_exists($key, $chain)) {
      throw new Exception('cascade chain is not found. try remove()');
    } else {
      $this->begin();
      $models = array();
      foreach ($chain[$key] as $tblName) {
        $foreignKey = "{$this->table}_{$this->primaryKey}";
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
    $key = $children[0]->connectName . ':' . $children[0]->table;

    if (array_key_exists($key, $chain)) {
      $references = array();
      foreach ($chain[$key] as $tblName) {
        $models = array();
        foreach ($children as $child) {
          $foreignKey = "{$child->table}_{$child->primaryKey}";
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

    $models  = array();
    $tblName = $this->table;

    foreach ($resultSet as $row) {
      $cloned = $model = $this->newClass($tblName);
      $cloned->setProperties($row);
      $models[] = $cloned;
    }
    return $models;
  }

  private function getExecuter()
  {
    return new Sabel_DB_Executer($this->property);
  }
}
