<?php

Sabel::using('Sabel_DB_Executer');
Sabel::using('Sabel_DB_Type_Const');
Sabel::using('Sabel_DB_SimpleCache');

if (!defined('TEST_CASE')) {
  Sabel::fileUsing(RUN_BASE . '/config/connection_map.php', true);
}

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
  const UPDATE_TIME_COLUMN = 'auto_update';
  const CREATE_TIME_COLUMN = 'auto_create';

  private
    $columns = array();

  private
    $data     = array(),
    $newData  = array(),
    $selected = false;

  private
    $parentModels     = array(),
    $acquiredParents  = array(),
    $cascadeStack     = array();

  protected
    $table       = '',
    $connectName = '',
    $structure   = 'normal',
    $withParent  = false,
    $myChildren  = array();

  protected
    $ignoreEmptyParent = false;

  protected
    $selectConditions = array(),
    $childConditions  = array(),
    $childConstraints = array();

  protected
    $validateOnInsert = false,
    $validateOnUpdate = false;

  protected $validateMessages = array('invalid_length'      => 'invalid length',
                                      'impossible_to_empty' => 'impossible to empty',
                                      'type_mismatch'       => 'invalid data type');

  /**
   * @var a schema information of DB usually use for validate.
   *      this value will instanciate in validate() method.
   */
  private $schema = null;

  /**
   * @var columns of Schema
   *
   */
  private $sColumns = array();

  /**
   * @var instance of Sabel_Errors
   *
   */
  protected $errors = null;

  /**
   * @var localize column names for errors
   *
   */
  protected $localize = array();

  public function __construct($param1 = null, $param2 = null)
  {
    $this->initialize();
    if (!empty($param1)) $this->defaultSelectOne($param1, $param2);
  }

  public function initialize($mdlName = null)
  {
    $mdlName = ($mdlName === null)  ? get_class($this) : $mdlName;
    $this->initSchema($mdlName);
  }

  protected function initSchema($mdlName)
  {
    $tblName = ($this->table === '') ? convert_to_tablename($mdlName) : $this->table;
    $cache   = Sabel_DB_SimpleCache::get('schema_' . $tblName);

    if ($cache) {
      $this->schema  = $cache;
      $this->columns = Sabel_DB_SimpleCache::get('columns_' . $tblName);
      $properties    = Sabel_DB_SimpleCache::get('props_'   . $tblName);
    } else {
      $sClsName = 'Schema_' . $mdlName;
      Sabel::using($sClsName);

      if (class_exists($sClsName, false)) {
        list($tblSchema, $properties) = $this->getSchemaFromCls($sClsName, $tblName);
      } else {
        list($tblSchema, $properties) = $this->getSchemaFromDb($tblName);
      }

      $columns = array_keys($tblSchema->getColumns());

      Sabel_DB_SimpleCache::add('schema_'  . $tblName, $tblSchema);
      Sabel_DB_SimpleCache::add('columns_' . $tblName, $columns);
      Sabel_DB_SimpleCache::add('props_'   . $tblName, $properties);

      if ($properties['primaryKey'] === null)
        trigger_error('primary key not found in ' . $properties['table'], E_USER_NOTICE);

      $this->schema  = $tblSchema;
      $this->columns = $columns;
    }

    $this->tableProp = new Sabel_ValueObject($properties);
  }

  public function __clone()
  {
    if ($this->errors !== null) {
      $this->errors = clone $this->errors;
    }
  }

  public function __set($key, $val)
  {
    $this->data[$key] = $val;

    if ($this->selected && in_array($key, $this->columns)) {
      $this->newData[$key] = $val;
    }
  }

  public function setProperties($row)
  {
    if (!is_array($row)) {
      $errorMsg = 'Sabel_DB_Property::setProperties(). argument should be an array.';
      throw new Exception($errorMsg);
    }
    foreach ($row as $key => $val) $this->data[$key] = $val;
  }

  public function setChildCondition($arg1, $arg2 = null, $arg3 = null)
  {
    if (is_object($arg1) || is_array($arg1)) {
      $this->childConditions[] = $arg1;
    } else {
      Sabel::using('Sabel_DB_Condition');
      $condition = new Sabel_DB_Condition($arg1, $arg2, $arg3);
      $this->childConditions[$condition->key] = $condition;
    }
  }

  public function setChildConstraint($mdlName, $constraints)
  {
    if (!is_array($constraints))
      throw new Exception('Error:setChildConstraint() second argument must be an array.');

    foreach ($constraints as $key => $val) {
      $this->childConstraints[$mdlName][$key] = $val;
    }
  }

  public function __get($key)
  {
    if (!isset($this->data[$key])) return null;
    return $this->convertData($key, $this->data[$key]);
  }

  protected function convertData($key, $data)
  {
    $schema = $this->schema->getColumns();
    if (!isset($schema[$key])) return $data;

    switch ($schema[$key]->type) {
      case Sabel_DB_Type_Const::INT:
        return (int)$data;
      case Sabel_DB_Type_Const::FLOAT:
      case Sabel_DB_Type_Const::DOUBLE:
        return (float)$data;
      case SabeL_DB_Type_Const::BOOL:
        if (is_int($data)) {
          return ($data === 1);
        } elseif(is_string($data)) {
          return ($data === '1' || $data === 'true');
        }
      default:
        return $data;
    }
  }

  public function getRealData()
  {
    $real = array();
    foreach ($this->data as $key => $val) {
      if (in_array($key, $this->columns)) $real[$key] = $this->convertData($key, $val);
    }
    return $real;
  }

  public function toArray()
  {
    return $this->data;
  }

  public function getData()
  {
    return $this->data;
  }

  public function getLocalizedName($name)
  {
    return (isset($this->localize[$name])) ? $this->localize[$name] : $name;
  }

  public function getStructure()
  {
    return $this->structure;
  }

  public function getMyChildren()
  {
    return $this->myChildren;
  }

  public function isSelected()
  {
    return $this->selected;
  }

  public function getChildConstraint()
  {
    return $this->childConstraints;
  }

  public function getChildCondition()
  {
    return $this->childConditions;
  }

  public function unsetChildCondition()
  {
    $this->childConditions  = array();
    $this->childConstraints = array();
  }

  public function enableParent()
  {
    $this->withParent = true;
  }

  public function validateOnInsert($bool)
  {
    $this->validateOnInsert = $bool;
  }

  public function validateOnUpdate($bool)
  {
    $this->validateOnUpdate = $bool;
  }

  public function schema($tblName = null)
  {
    if (isset($tblName)) return $this->getTableSchema($tblName)->getColumns();

    $columns = $this->schema->getColumns();
    foreach ($this->data as $name => $value) {
      if (isset($columns[$name])) {
        $columns[$name]->value = $this->convertData($name, $value);
      }
    }

    return $columns;
  }

  public function hasError()
  {
    return $this->errors->hasError();
  }

  public function getErrors()
  {
    return $this->errors;
  }

  public function getTableEngine()
  {
    return $this->tableProp->tableEngine;
  }

  public function getTableSchema($tblName = null)
  {
    return ($tblName === null) ? $this->schema : parent::getTableSchema($tblName);
  }

  public function getColumnNames($tblName = null)
  {
    return ($tblName === null) ? $this->columns : parent::getColumnNames($tblName);
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
    return $this->createModel(clone $this);
  }

  protected function createModel($model)
  {
    $p = $model->getProjection();
    $model->getStatement()->setBasicSQL("SELECT $p FROM " . $model->tableProp->table);

    if ($row = $model->exec()->fetch()) {
      $model->transrate(($this->withParent) ? $this->addParent($row) : $row);
      $model->getDefaultChild($model);
    } else {
      $model->selectConditions = $model->conditions;
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
    $tblName = $this->tableProp->table;

    if ($this->withParent) {
      $relClass = Sabel::load('Sabel_DB_Model_Relation');
      $mdlName  = convert_to_modelname($tblName);
      if ($relClass->initJoin($mdlName)) return $relClass->execJoin($this, 'INNER');
    }

    $p = $this->getProjection();
    $this->getStatement()->setBasicSQL("SELECT $p FROM $tblName");

    $resultSet = $this->exec();
    if ($resultSet->isEmpty()) return false;

    $models = array();
    $ccond  = $this->getChildConstraint();
    $obj    = MODEL(convert_to_modelname($tblName));
    $rows   = $resultSet->fetchAll();

    foreach ($rows as $row) {
      $model = clone $obj;

      if ($ccond) $model->childConstraints = $ccond;

      $model->transrate(($this->withParent) ? $this->addParent($row) : $row);
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
    $tblName = strtolower($tblName);
    if ($this->structure !== 'tree' && $this->isAcquired($tblName)) return false;

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
      $p = $model->getProjection();
      $model->getStatement()->setBasicSQL("SELECT $p FROM $tblName");
      $resultSet = $model->exec();

      if ((!$row = $resultSet->fetch()) && !$this->ignoreEmptyParent) {
        $msg = 'Error: relational error. parent does not exist. '
             . 'if you mean it try ignoreEmptyParent.';

        throw new Exception($msg);
      }

      Sabel_DB_SimpleCache::add($cacheName, $row);
    }

    $row = $this->addParentModels($row, $model->tableProp->primaryKey);
    $model->transrate($row);
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

    $cModel = MODEL($child);
    $p = $cModel->getProjection();
    $cModel->getStatement()->setBasicSQL("SELECT $p FROM " . $cModel->tableProp->table);

    $this->chooseChildConstraint($child, $model);
    $primary = $model->tableProp->primaryKey;
    $model->setChildCondition("{$model->tableProp->table}_{$primary}", $model->$primary);

    $cModel->conditions = $model->getChildCondition();
    $cconst = $model->getChildConstraint();
    if (isset($cconst[$child])) $cModel->constraints = $cconst[$child];

    $resultSet = $cModel->exec();

    if ($resultSet->isEmpty()) {
      return $model->$child = false;
    }

    $withParent = ($this->withParent || $cModel->withParent);

    $children = array();
    $childObj = MODEL($child);
    $rows     = $resultSet->fetchAll();

    foreach ($rows as $row) {
      $childObj = clone $childObj;
      $childObj->transrate(($withParent) ? $this->addParent($row) : $row);
      $this->getDefaultChild($childObj);
      $children[] = $childObj;
    }

    $model->$child = $children;
    return $children;
  }

  protected function getDefaultChild($model)
  {
    if ($children = $model->getMyChildren()) {
      foreach ($children as $child) {
        $this->chooseChildConstraint($child, $model);
        $model->getChild($child, $model);
      }
    }
  }

  protected function chooseChildConstraint($child, $model)
  {
    $constraints = array();
    $thisCConst  = $this->getChildConstraint();
    $modelCConst = $model->getChildConstraint();

    if (isset($thisCConst[$child])) {
      $constraints = $thisCConst[$child];
    } elseif (isset($modelCConst[$child])) {
      $constraints = $modelCConst[$child];
    }

    if ($constraints) $model->setChildConstraint($child, $constraints);

    if ($thisCConst)  {
      foreach ($thisCConst as $cldName => $param) {
        $model->setChildConstraint($cldName, $param);
      }
    }
  }

  /**
   * transrating row of table to properties of Model
   *
   * @param array $row row data
   */
  public function transrate($row)
  {
    $pKey = $this->tableProp->primaryKey;

    if (is_array($pKey)) {
      foreach ($pKey as $key) {
        $condition = new Sabel_DB_Condition($key, $row[$key]);
        $this->selectConditions[$key] = $condition;
      }
    } else {
      if (isset($row[$pKey])) {
        $condition = new Sabel_DB_Condition($pKey, $row[$pKey]);
        $this->selectConditions[$pKey] = $condition;
      }
    }

    $this->setProperties($row);
    $this->selected = true;
  }

  public function newChild($child = null)
  {
    $id = $this->{$this->tableProp->primaryKey};

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

    if (isset($this->data[$pkey])) {
      $id = $this->data[$pkey];
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
      if ($this->validateOnUpdate) {
        if (($this->errors = $this->validate())) return false;
      }

      $saveData = ($data) ? $data : $this->newData;
      $this->recordTime($saveData, $tblName, self::UPDATE_TIME_COLUMN);
      $this->conditions = $this->selectConditions;
      $this->update($saveData);
      $newData = array_merge($this->getRealData(), $saveData);
      $this->newData = array();
    } else {
      if ($this->validateOnInsert) {
        if (($this->errors = $this->validate())) return false;
      }

      $driver  = $this->getDriver();
      $stmt    = $this->getStatement();
      $newData = ($data) ? $data : $this->data;

      $this->recordTime($newData, $tblName, self::UPDATE_TIME_COLUMN);
      $this->recordTime($newData, $tblName, self::CREATE_TIME_COLUMN);

      $incCol = $this->tableProp->incrementKey;

      try {
        $this->execInsert($driver, $stmt, $newData, $incCol);
      } catch (Exception $e) {
        $this->executeError($e->getMessage(), $driver);
      }

      if ($incCol) {
        $newId = $driver->getLastInsertId();
        if (!isset($newData[$incCol])) $newData[$incCol] = $newId;
      }
    }

    foreach ($newData as $key => $val) $newModel->$key = $val;

    $newModel->selected = true;
    return $newModel;
  }

  protected function recordTime(&$data, $tblName, $colName)
  {
    if (in_array($colName, $this->columns)) {
      if (!isset($data[$colName])) $data[$colName] = date('Y-m-d H:i:s');
    }
  }

  public function validate()
  {
    $this->sColumns  = $this->schema->getColumns();
    $this->errors    = $errors = Sabel::load('Sabel_Errors');
    $dataForValidate = ($this->isSelected()) ? $this->newData : $this->data;
    
    foreach ($dataForValidate as $name => $value) {
      $lname = $this->getLocalizedName($name);
      if ($this->validateLength($name, $value)) {
        $errors->add($lname, $this->validateMessages["invalid_length"]);
      } elseif ($this->validateNullable($name, $value)) {
        $errors->add($lname, $this->validateMessages["impossible_to_empty"]);
      } elseif ($this->validateType($name, $value)) {
        $errors->add($lname, $this->validateMessages["type_mismatch"]);
      } elseif ($this->hasValidateMethod($name)) {
        $this->executeValidateMethod($name, $value);
      }
    }

    $nonInputs = $this->validateNonInputs($dataForValidate);
    foreach ($nonInputs as $name) {
      $name = $this->getLocalizedName($name);
      $errors->add($name, $this->validateMessages["impossible_to_empty"]);
    }
    
    return ($errors->count() !== 0) ? $errors : false;
  }

  protected function hasValidateMethod($name)
  {
    return (method_exists($this, 'validate' . ucfirst($name)));
  }

  protected function executeValidateMethod($name, $value)
  {
    $methodName = 'validate' . ucfirst($name);
    return $this->$methodName($name, $value);
  }

  protected function validateLength($name, $value)
  {
    $type = $this->sColumns[$name]->type;
    if ($type === Sabel_DB_Type_Const::INT || $type === Sabel_DB_Type_Const::STRING) {
      return ($this->sColumns[$name]->max < strlen($value));
    } else {
      return false;
    }
  }

  protected function validateNullable($name, $value)
  {
    $result = false;
    if ($this->sColumns[$name]->nullable === false) {
      if ($value === null || $value === "") $result = true;
    }
    return $result;
  }

  public function validateType($name, $value)
  {

    switch ($this->sColumns[$name]->type) {
      case Sabel_DB_Type_Const::INT:
        return (!ctype_digit($value));
        break;
      case Sabel_DB_Type_Const::BOOL:
        return ($value !== __TRUE__ && $value !== __FALSE__);
        break;
      case Sabel_DB_Type_Const::DATETIME:
        return (boolean) strtotime($value);
        break;
      default:
        return false;
        break;
    }
  }

  protected function validateNonInputs($dataForValidate)
  {
    $impossibleToNulls = array();

    foreach ($this->schema as $s) {
      if (!$s->increment && !$s->nullable) $impossibleToNulls[] = $s->name;
    }

    return array_diff($impossibleToNulls, array_keys($dataForValidate));
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
      $scond   = $this->selectConditions;
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

    $id    = (isset($id)) ? $id : $this->{$this->tableProp->primaryKey};
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

  protected function getSchemaFromCls($clsName, $tblName)
  {
    Sabel::using('Sabel_DB_Schema_Table');

    $cols = array();
    $sCls = new $clsName();
    foreach ($sCls->get() as $colName => $colInfo) {
      $colInfo['name'] = $colName;
      $cols[$colName]  = new Sabel_ValueObject($colInfo);
    }

    $tblSchema  = new Sabel_DB_Schema_Table($tblName, $cols);
    $properties = $sCls->getProperty();
    $properties['table'] = $tblName;
    $properties['connectName'] = get_db_tables($tblName);

    return array($tblSchema, $properties);
  }

  protected function getSchemaFromDb($tblName)
  {
    $conName    = get_db_tables($tblName);
    $scmName    = Sabel_DB_Connection::getSchema($conName);
    $database   = Sabel_DB_Connection::getDB($conName);
    $accessor   = Sabel::load('Sabel_DB_Schema_Accessor', $conName, $scmName);
    $engine     = ($database === 'mysql') ? $accessor->getTableEngine($tblName) : null;
    $tblSchema  = $accessor->getTable($tblName);

    $properties = array('connectName'  => $conName,
                        'primaryKey'   => $tblSchema->getPrimaryKey(),
                        'incrementKey' => $tblSchema->getIncrementKey(),
                        'tableEngine'  => $engine,
                        'table'        => $tblName);

    return array($tblSchema, $properties);
  }

  /**
   * an alias for setChildConstraint.
   *
   */
  public function cconst($mdlName, $constraints)
  {
    $this->setChildConstraint($mdlName, $constraints);
  }

  /**
   * an alias for setChildCondition.
   *
   */
  public function ccond($arg1, $arg2 = null)
  {
    $this->setChildCondition($arg1, $arg2);
  }
}
