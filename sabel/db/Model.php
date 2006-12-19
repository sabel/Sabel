<?php

Sabel::using('Sabel_ValueObject');
Sabel::using('Sabel_DB_Executer');
Sabel::using('Sabel_DB_Type_Const');
Sabel::using('Sabel_DB_Model_Property');

/**
 * Sabel_DB_Model
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 *               Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model extends Sabel_DB_Executer
{
  const UPDATE_TIME_COLUMN = 'auto_update';
  const CREATE_TIME_COLUMN = 'auto_create';

  protected
    $property = null;

  private
    $refStructure    = array(),
    $acquiredParents = array(),
    $cascadeStack    = array();

  private
    $parentModels = array();
    
  /**
   * @var a schema information of DB usually use for validate.
   *      this value will instanciate in validate() method.
   */
  private $schema = null;
  
  /**
   * @var instance of Sabel_Errors
   */
  protected $errors = null;
  
  protected $validateOnInsert = false;
  protected $validateOnUpdate = false;
  
  protected $validateMessages = array("invalid_length"      => "invalid length",
                                      "impossible_to_empty" => "impossible to empty",
                                      "type_mismatch"       => "invalid data type");
  
  /**
   * @var localize column names for errors
   */
  protected $localize = array();
  
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
    if (isset($tblName)) return $this->getTableSchema($tblName)->getColumns();

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

    if ($row = $model->exec()->fetch()) {
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
      if ($relClass->initJoin($mdlName))
        return $relClass->execJoin($this, 'INNER');
    }

    $projection = $this->property->getProjection();
    $this->getStatement()->setBasicSQL("SELECT $projection FROM $tblName");

    $resultSet = $this->exec();
    if ($resultSet->isEmpty()) return false;

    $childConstraints = $this->property->getChildConstraint();

    $models = array();
    $obj    = MODEL(convert_to_modelname($tblName));
    $rows   = $resultSet->fetchAll();

    foreach ($rows as $row) {
      $model = clone $obj;

      if ($childConstraints) $model->receiveChildConstraint($childConstraints);

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

    $cacheName = $tblName . $id;
    if (!is_array($row = Sabel_DB_SimpleCache::get($cacheName))) {
      $model->setCondition($model->tableProp->primaryKey, $id);
      $projection = $model->property->getProjection();
      $model->getStatement()->setBasicSQL("SELECT $projection FROM $tblName");
      $resultSet = $model->exec();

      if (!$row = $resultSet->fetch())
        throw new Exception('Error: relational error. parent does not exists.');

      Sabel_DB_SimpleCache::add($cacheName, $row);
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

    $resultSet = $cModel->exec();

    if ($resultSet->isEmpty()) {
      $model->property->set($child, false);
      return false;
    }

    $withParent = ($this->property->isWithParent() || $cModel->property->isWithParent());

    $children = array();
    $childObj = MODEL($child);
    $rows     = $resultSet->fetchAll();

    foreach ($rows as $row) {
      $childObj = clone $childObj;
      $childObj->setData(($withParent) ? $this->addParent($row) : $row);
      $this->getDefaultChild($childObj);
      $children[] = $childObj;
    }

    $model->property->set($child, $children);
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

    $model = MODEL($child);
    $model->setCondition("{$this->tableProp->table}_{$pkey}", $id);
    $model->doDelete();
  }

  public function save($data = null)
  {
    if (isset($data) && !is_array($data)) {
      throw new Exception('Error:save() argument must be an array');
    }

    $tblName  = $this->tableProp->table;
    $newModel = MODEL(convert_to_tablename($tblName));

    if ($this->isSelected()) {
      // process update.
      if ($this->validateOnUpdate) {
        if (($this->errors = $this->validate())) {
          return false;
        }
      }
      $saveData = ($data) ? $data : $this->property->getNewData();
      $this->recordTime($saveData, $tblName, self::UPDATE_TIME_COLUMN);
      $this->conditions = $this->property->getSelectCondition();
      $this->update($saveData);
      $this->property->unsetNewData();
      $newData = array_merge($this->property->getRealData(), $saveData);
    } else {
      // process insert.
      if ($this->validateOnInsert) {
        if (($this->errors = $this->validate())) {
          return false;
        }
      }
      $newData = ($data) ? $data : $this->property->getData();
      $this->recordTime($newData, $tblName, self::UPDATE_TIME_COLUMN);
      $this->recordTime($newData, $tblName, self::CREATE_TIME_COLUMN);
      if ($incCol = $this->checkIncColumn()) {
        $newId = $this->insert($newData, $incCol);
        if (!isset($newData[$incCol])) $newData[$incCol] = $newId;
      } else {
        $this->insert($newData);
      }
    }

    foreach ($newData as $key => $val) $newModel->property->set($key, $val);

    $newModel->enableSelected();
    return $newModel;
  }
  
  public function hasError()
  {
    return $this->errors->hasError();
  }
  
  public function getErrors()
  {
    return $this->errors;
  }
  
  protected function hasValidateMethod($name)
  {
    return (method_exists($this, 'validate'.ucfirst($name)));
  }
  
  protected function executeValidateMethod($name, $value)
  {
    $methodName = "validate".ucfirst($name);
    return $this->$methodName($name, $value);
  }
  
  /**
   * validate with schema
   *
   */
  public function validate()
  {
    // instanciate schema
    if ($this->schema === null) {
      $this->schema = $this->property->getSchema()->getColumns();
    }
    
    $this->errors = $errors = Sabel::load('Sabel_Errors');
    
    $dataForValidate = $this->property->getValidateData();
    
    $errorOccur = false;
    foreach ($dataForValidate as $name => $value) {
      if ($this->validateLength($name, $value)) {
        if (isset($this->localize[$name])) $name = $this->localize[$name];
        $errors->add($name, $this->validateMessages["invalid_length"]);
        $errorOccur = true;
      } elseif ($this->validateNullable($name, $value)) {
        if (isset($this->localize[$name])) $name = $this->localize[$name];
        $errors->add($name, $this->validateMessages["impossible_to_empty"]);
        $errorOccur = true;
      } elseif ($this->validateType($name, $value)) {
        if (isset($this->localize[$name])) $name = $this->localize[$name];
        $errors->add($name, $this->validateMessages["type_mismatch"]);
        $errorOccur = true;
      } elseif ($this->hasValidateMethod($name)) {
        $this->executeValidateMethod($name, $value);
      }
    }
    
    $nonInputs = $this->validateNonInputs($dataForValidate);
    if ($nonInputs) {
      foreach ($nonInputs as $name) {
        if (isset($this->localize[$name])) $name = $this->localize[$name];
        $errors->add($name, $this->validateMessages["impossible_to_empty"]);
        $errorOccur = true;
      }
    }
    
    if ($errorOccur) {
      return $errors;
    } else {
      return false;
    }
  }
  
  public function validateOnInsert($bool)
  {
    $this->validateOnInsert = $bool;
  }
  
  public function validateOnUpdate($bool)
  {
    $this->validateOnUpdate = $bool;
  }
  
  protected function validateLength($name, $value)
  {
    $type = $this->schema[$name]->type;
    if ($type === Sabel_DB_Type_Const::INT || $type === Sabel_DB_Type_Const::STRING) {
      return ($this->schema[$name]->max < strlen($value));
    } else {
      return false;
    }
  }
  
  protected function validateNullable($name, $value)
  {
    $result = false;
    if ($this->schema[$name]->nullable === false) {
      if ($value === null || $value === "") {
        $result = true;
      }
    }
    return $result;
  }
  
  public function validateType($name, $value)
  {
    $result = false;
    switch ($this->schema[$name]->type) {
      case Sabel_DB_Type_Const::INT:
        if (!is_numeric($value))  return true;
        if (!ctype_digit($value)) return true;
        break;
      case Sabel_DB_Type_Const::BOOL:
        if ($value === __TRUE__ || $value === __FALSE__) {
          return false;
        } else {
          return true;
        }
        break;
      case Sabel_DB_Type_Const::DATETIME:
        
        break;
    }
    return $result;
  }
  
  protected function validateNonInputs($dataForValidate)
  {
    $impossibleToNulls = array();
    foreach ($this->schema as $s) {
      if (!$s->increment && !$s->nullable) {
        $impossileToNulls[] = $s->name;
      }
    }
    
    $noninputs = array_diff($impossileToNulls, array_keys($dataForValidate));
    
    if (count($noninputs)) {
      return $noninputs;
    } else {
      return false;
    }
  }
  
  protected function recordTime(&$data, $tblName, $colName)
  {
    $cols = $this->property->getColumns();
    if (in_array($colName, $cols)) {
      if (!isset($data[$colName])) $data[$colName] = date('Y-m-d H:i:s');
    }
  }

  public function multipleInsert($data)
  {
    if (!is_array($data)) {
      throw new Exception('Error:multipleInsert() data is not array.');
    }

    $this->begin();

    try {
      $this->ArrayInsert($data);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }

    $this->commit();
  }

  public function remove($param1 = null, $param2 = null, $param3 = null)
  {
    if ($param1 !== null) {
      $this->delete($param1, $param2, $param3);
    } else {
      $pKey    = $this->tableProp->primaryKey;
      $scond   = $this->getSelectCondition();
      $idValue = (isset($scond[$pKey])) ? $scond[$pKey]->value : null;

      $this->delete((isset($idValue)) ? $pKey : null, $idValue);
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
    $key   = $this->getConnectName() . ':' . $this->tableProp->table;

    if (!isset($chain[$key]))
      throw new Exception("Sabel_DB_Relation::cascadeDelete() $key is not found. try remove()");

    $this->begin();

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

    $this->commit();
  }

  private function makeChainModels($children, &$chain)
  {
    $conName = $children[0]->getConnectName();
    $tblName = $children[0]->getTableName();
    $key     = $conName . ':' . $tblName;

    if (!isset($chain[$key])) return null;

    $models = array();
    foreach ($chain[$key] as $tblName) {
      foreach ($children as $child) {
        $foreignKey = $child->tableProp->table . '_' . $child->tableProp->primaryKey;
        if ($model = $this->pushStack($tblName, $foreignKey, $child->id)) $models[] = $model;
      }
    }

    if ($models) {
      foreach ($models as $children) $this->makeChainModels($children, $chain);
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

      $model->begin();
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
