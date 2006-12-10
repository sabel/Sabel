<?php

Sabel::using('Sabel_ValueObject');
Sabel::using('Sabel_DB_Executer');
Sabel::using('Sabel_DB_Model_Property');

/**
 * Sabel_DB_Model
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model extends Sabel_DB_Executer
{
  protected
    $property = null;

  private
    $refStructure    = array(),
    $acquiredParents = array(),
    $cascadeStack    = array();

  private
    $parentModels = array();

  public function __construct($param1 = null, $param2 = null)
  {
    if ($this->property === null) $this->createProperty();
    if (!empty($param1)) $this->defaultSelectOne($param1, $param2);
  }

  protected function createProperty($mdlName = null, $mdlProps = null)
  {
    $mdlName  = ($mdlName === null)  ? get_class($this) : $mdlName;
    $mdlProps = ($mdlProps === null) ? get_object_vars($this) : $mdlProps;

    $this->property  = new Sabel_DB_Model_Property($mdlName, $mdlProps);
    $tableProperties = $this->property->getTableProperties();
    $this->tableProp = new Sabel_ValueObject($tableProperties);
  }

  public function __set($key, $val)
  {
    $this->property->$key = $val;
  }

  public function __get($key)
  {
    return $this->property->$key;
  }

  public function __clone()
  {
    $this->property = clone $this->property;
  }

  public function __call($method, $parameters)
  {
    if ($this->property === null) $this->createProperty();
    @list($arg1, $arg2, $arg3) = $parameters;
    return $this->property->$method($arg1, $arg2, $arg3);
  }

  public function schema($tblName = null)
  {
    if (isset($tblName)) {
      return $this->getTableSchema($tblName)->getColumns();
    }

    $columns = $this->getSchema()->getColumns();
    foreach ($this->property->getData() as $name => $value) {
      if (isset($columns[$name])) {
        $columns[$name]->value = $this->property->convertData($name, $value);
      }
    }

    return $columns;
  }

  public function getTableEngine()
  {
    return $this->tableProp->tableEngine;
  }

  public function getTableSchema($tblName = null)
  {
    return ($tblName === null) ? $this->property->getSchema()
                               : parent::getTableSchema($tblName);
  }

  public function getColumnNames($tblName = null)
  {
    return ($tblName === null) ? $this->property->getColumns()
                               : parent::getColumnNames($tblName);
  }

  protected function defaultSelectOne($param1, $param2 = null)
  {
    $this->setCondition($param1, $param2);
    $this->createModel($this);
  }

  protected function getEdge($order, $orderColumn)
  {
    $this->setCondition($orderColumn, Sabel_DB_Condition::NOTNULL);
    $this->setConstraint(array('limit' => 1, 'order' => "$orderColumn $order"));
    return $this->selectOne();
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
    if ($param1 === null && empty($this->conditions))
      throw new Exception('Error: selectOne() [WHERE] must be set condition.');

    $this->setCondition($param1, $param2, $param3);

    $property = $this->property;
    $self = clone $this;
    $self->property = $property;

    return $this->createModel($self);
  }

  protected function createModel($model)
  {
    $projection = $model->property->getProjection();
    $model->getStatement()->setBasicSQL("SELECT $projection FROM " . $model->tableProp->table);

    if ($row = $model->find()->fetch()) {
      $withParent = $model->property->isWithParent();
      $model->setData(($withParent) ? $this->addParent($row) : $row);
      $model->getDefaultChild($model);
    } else {
      $model->receiveSelectCondition($model->conditions);
      foreach ($model->conditions as $condition) {
        $model->{$condition->key} = $condition->value;
      }
    }
    return $model;
  }

  /**
   * retrieve rows
   *
   * @param  mixed    $param1 column name ( with the condition prefix ), or value of primary key.
   * @param  mixed    $param2 condition value.
   * @param  constant $param3 denial ( Sabel_DB_Condition::NOT )
   * @return mixed    array or false.
   */
  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setCondition($param1, $param2, $param3);

    $tblName    = $this->tableProp->table;
    $withParent = $this->property->isWithParent();

    if ($withParent) {
      $relClass = Sabel::load('Sabel_DB_Model_Relation');
      $mdlName  = convert_to_modelname($tblName);
      if ($relClass->initJoin($mdlName)) {
        return $relClass->execJoin($this, 'INNER');
      }
    }

    $projection = $this->property->getProjection();
    $this->getStatement()->setBasicSQL("SELECT $projection FROM $tblName");

    $resultSet = $this->find();
    if ($resultSet->isEmpty()) return false;

    $childConstraints = $this->property->getChildConstraint();

    $models = array();
    $obj    = MODEL(convert_to_modelname($tblName));
    $rows   = $resultSet->fetchAll();

    foreach ($rows as $row) {
      $model = clone $obj;

      if ($childConstraints) {
        $model->receiveChildConstraint($childConstraints);
      }

      $model->setData(($withParent) ? $this->addParent($row) : $row);
      $this->getDefaultChild($model);
      $models[] = $model;
    }
    return $models;
  }

  /**
   * retrieve rows from table by join query of some types.
   *
   * @param  array  $modelPairs model pairs. (ex. 'Hoge:Huga', 'Hoge:Foo', 'Foo:Bar'
   * @param  string $joinType   'INNER'( default ) or 'LEFT' or 'RIGHT'
   * @param  array  $colList    key is model name. and set the columns name in it.
   * @return array
   */
  public function selectJoin($modelPairs, $joinType = 'INNER', $colList = null)
  {
    if (!is_array($modelPairs))
      throw new Exception('Error: joinSelect() argument must be an array.');

    $relClass = Sabel::load('Sabel_DB_Model_Relation');
    return $relClass->join($this, $modelPairs, $joinType, $colList);
  }

  protected function addParent($row)
  {
    $this->acquiredParents = array($this->tableProp->table);
    return $this->addParentModels($row, $this->tableProp->primaryKey);
  }

  protected function addParentModels($row, $pKey)
  {
    foreach ($row as $key => $val) {
      if (strpos($key, "_{$pKey}") !== false) {
        $tblName = str_replace("_{$pKey}", '', $key);
        $result  = $this->createParentModels($tblName, $val);
        if ($result) {
          $mdlName = convert_to_modelname($tblName);
          $row[$mdlName] = $result;
        }
      }
    }
    return $row;
  }

  private function createParentModels($tblName, $id)
  {
    $tblName   = strtolower($tblName);
    $structure = $this->property->getStructure();
    if ($structure !== 'tree' && $this->isAcquired($tblName)) return false;

    if (isset($this->parentModels[$tblName])) {
      $model = clone $this->parentModels[$tblName];
    } else {
      $model = MODEL(convert_to_modelname($tblName));
      $this->parentModels[$tblName] = $model;
    }

    if ($id === null) return $model;

    if (!is_array($row = Sabel_DB_SimpleCache::get($tblName . $id))) {
      $model->setCondition($model->tableProp->primaryKey, $id);
      $projection = $model->property->getProjection();
      $model->getStatement()->setBasicSQL("SELECT $projection FROM $tblName");
      $resultSet = $model->find();

      if (!$row = $resultSet->fetch()) {
        throw new Exception('Error: relational error. parent does not exists.');
      }

      Sabel_DB_SimpleCache::add($tblName . $id, $row);
    }

    $row = $this->addParentModels($row, $model->tableProp->primaryKey);
    $model->setData($row);
    return $model;
  }

  private function isAcquired($tblName)
  {
    if (in_array($tblName, $this->acquiredParents)) return true;
    $this->acquiredParents[] = $tblName;
    return false;
  }

  /**
   * fetch the children by relating own primary key to foreign key of a given table name.
   *
   * @param  string $child model name.
   * @param  mixed  $model need not be used. ( used internally )
   * @return array
   */
  public function getChild($child, $model = null)
  {
    if ($model === null) $model = $this;

    $cModel     = MODEL($child);
    $projection = $cModel->property->getProjection();
    $cModel->getStatement()->setBasicSQL("SELECT $projection FROM " . $cModel->tableProp->table);

    $this->chooseChildConstraint($child, $model);
    $primary = $model->tableProp->primaryKey;
    $model->setChildCondition("{$model->tableProp->table}_{$primary}", $model->$primary);

    $cModel->conditions = $model->property->getChildCondition();
    $cconst = $model->property->getChildConstraint();
    if (isset($cconst[$child])) $cModel->constraints = $cconst[$child];

    $resultSet = $cModel->find();

    if ($resultSet->isEmpty()) {
      $model->dataSet($child, false);
      return false;
    }

    $withParent = $this->property->isWithParent();
    $withParent = ($withParent) ? true : $cModel->property->isWithParent();

    $children = array();
    $childObj = MODEL($child);
    $rows     = $resultSet->fetchAll();

    foreach ($rows as $row) {
      $childObj = clone $childObj;
      $childObj->setData(($withParent) ? $this->addParent($row) : $row);
      $this->getDefaultChild($childObj);
      $children[] = $childObj;
    }

    $model->dataSet($child, $children);
    return $children;
  }

  protected function getDefaultChild($model)
  {
    if ($children = $model->property->getMyChildren()) {
      foreach ($children as $val) {
        $this->chooseChildConstraint($val, $model);
        $model->getChild($val, $model);
      }
    }
  }

  protected function chooseChildConstraint($child, $model)
  {
    $thisCConst  = $this->property->getChildConstraint();
    $modelCConst = $model->property->getChildConstraint();

    $constraints = array();
    if (isset($thisCConst[$child])) {
      $constraints = $thisCConst[$child];
    } elseif (isset($modelCConst[$child])) {
      $constraints = $modelCConst[$child];
    }

    if ($constraints) $model->property->setChildConstraint($child, $constraints);

    if ($thisCConst)  {
      foreach ($thisCConst as $cldName => $param) {
        $model->property->setChildConstraint($cldName, $param);
      }
    }
  }

  public function setData($row)
  {
    $pKey = $this->tableProp->primaryKey;

    if (is_array($pKey)) {
      foreach ($pKey as $key) {
        $condition = new Sabel_DB_Condition($key, $row[$key]);
        $this->setSelectCondition($key, $condition);
      }
    } else {
      if (isset($row[$pKey])) {
        $condition = new Sabel_DB_Condition($pKey, $row[$pKey]);
        $this->setSelectCondition($pKey, $condition);
      }
    }

    $this->setProperties($row);
    $this->enableSelected();
  }

  public function newChild($child = null)
  {
    $data = $this->getData();
    $id   = $data[$this->tableProp->primaryKey];

    if (empty($id)) {
      throw new Exception("Error:newChild() who is a parent? hasn't id value.");
    }

    $parent  = $this->tableProp->table;
    $tblName = ($child === null) ? $parant : $child;
    $model   = MODEL(convert_to_modelname($tblName));
    $column  = "{$parent}_{$this->tableProp->primaryKey}";
    $model->$column = $id;
    return $model;
  }

  /**
   * remove all chilren.
   *
   * @param string $child child model name.
   * @return void
   */
  public function clearChild($child)
  {
    $pkey = $this->tableProp->primaryKey;
    $data = $this->property->getData();

    if (isset($data[$pkey])) {
      $id = $data[$pkey];
    } else {
      throw new Exception("Error:clearChild() who is a parent? hasn't id value.");
    }

    $model  = MODEL($child);
    $model->setCondition("{$this->tableProp->table}_{$pkey}", $id);

    // @todo
    $model->getStatement()->setBasicSQL('DELETE FROM ' . $model->tableProp->table);
    $model->getDriver()->makeQuery($model->conditions, $model->constraints);
    $model->tryExecute($model->driver);
  }

  public function save($data = null)
  {
    if (isset($data) && !is_array($data))
      throw new Exception('Error:save() argument must be an array');

    if ($this->isSelected()) {
      $saveData = ($data) ? $data : $this->property->getNewData();
      $this->conditions = $this->property->getSelectCondition();
      $this->update($saveData);
      $this->property->unsetNewData();
    } else {
      $saveData = ($data) ? $data : $this->property->getData();
      if ($incCol = $this->checkIncColumn()) {
        $newId = $this->insert($saveData, $incCol);
        $this->property->dataSet($incCol, $newId);
      } else {
        $this->insert($saveData);
      }
    }

    foreach ($saveData as $key => $val) $this->dataSet($key, $val);
    return $this;
  }

  public function multipleInsert($data)
  {
    if (!is_array($data)) {
      throw new Exception('Error:multipleInsert() data is not array.');
    }

    BEGIN($this);

    try {
      $this->ArrayInsert($data);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }

    COMMIT();
  }

  public function remove($param1 = null, $param2 = null, $param3 = null)
  {
    if ($param1 !== null) {
      parent::remove($param1, $param2, $param3);
    } else {
      $pKey    = $this->tableProp->primaryKey;
      $scond   = $this->getSelectCondition();
      $idValue = (isset($scond[$pKey])) ? $scond[$pKey]->value : null;

      parent::remove((isset($idValue)) ? $pKey : null, $idValue);
    }
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

    if ($id === null && !$this->isSelected())
      throw new Exception('Error: give the value of id or select the model beforehand.');

    $data  = $this->getData();
    $id    = (isset($id)) ? $id : $data[$this->tableProp->primaryKey];
    $chain = Schema_CascadeChain::get();
    $key   = $this->tableProp->connectName . ':' . $this->tableProp->table;

    if (!isset($chain[$key])) {
      throw new Exception("Sabel_DB_Relation::cascadeDelete() $key is not found. try remove()");
    }

    BEGIN($this);

    $models = array();
    $table  = $this->tableProp->table;
    $pKey   = $this->tableProp->primaryKey;
    foreach ($chain[$key] as $tblName) {
      $foreignKey = "{$table}_{$pKey}";
      if ($model = $this->pushStack($tblName, $foreignKey, $id)) $models[] = $model;
    }

    foreach ($models as $children) $this->makeChainModels($children, $chain);

    $this->clearCascadeStack(array_reverse($this->cascadeStack));
    $this->remove($pKey, $id);

    COMMIT();
  }

  private function makeChainModels($children, &$chain)
  {
    $conName = $children[0]->tableProp->connectName;
    $tblName = $children[0]->tableProp->table;
    $key     = $conName . ':' . $tblName;

    if (!isset($chain[$key])) return null;

    foreach ($chain[$key] as $tblName) {
      $models = array();
      foreach ($children as $child) {
        $foreignKey = $child->tableProp->table . '_' . $child->tableProp->primaryKey;
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
    $model  = MODEL(convert_to_modelname($tName));
    $model->setConnectName($cName);
    $models = $model->select($foreignKey, $id);

    if ($models) $this->cascadeStack["{$cName}:{$tName}:{$id}"] = $foreignKey;
    return $models;
  }

  private function clearCascadeStack($stack)
  {
    foreach ($stack as $param => $foreignKey) {
      list($cName, $tName, $idValue) = explode(':', $param);
      $model = MODEL(convert_to_modelname($tName));
      $model->setConnectName($cName);

      BEGIN($model);
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
    if (isset($param) && !is_array($param))
      throw new Exception('Error: execute() second argument must be an array');

    return $this->toObject($this->executeQuery($sql, $param));
  }

  protected function toObject($resultSet)
  {
    if ($resultSet->isEmpty()) return false;

    $models  = array();
    $tblName = $this->tableProp->table;
    $obj     = MODEL(convert_to_modelname($tblName));

    foreach ($resultSet as $row) {
      $model = clone $obj;
      $model->setProperties($row);
      $models[] = $model;
    }
    return $models;
  }
}
