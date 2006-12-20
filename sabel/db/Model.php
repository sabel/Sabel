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
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model extends Sabel_DB_Executer
{
  const UPDATE_TIME_COLUMN = 'auto_update';
  const CREATE_TIME_COLUMN = 'auto_create';

  protected
    $property = null;

  private
    $columns = array();
    
  private
    $parentModels    = array(),
    $acquiredParents = array(),
    $cascadeStack    = array();

  protected
    $validateOnInsert = false,
    $validateOnUpdate = false;

  protected $validateMessages = array("invalid_length"      => "invalid length",
                                      "impossible_to_empty" => "impossible to empty",
                                      "type_mismatch"       => "invalid data type");

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
    if ($this->property === null) $this->createProperty();
    if (!empty($param1)) $this->defaultSelectOne($param1, $param2);
  }
  
  protected function initSchema($mdlName, $conName, $tblName = '')
  {
    $tblName = ($tblName === '') ? convert_to_tablename($mdlName) : $tblName;
    $cache   = Sabel_DB_SimpleCache::get('schema_' . $tblName);
    
    if ($cache) {
      $this->schema  = $cache;
      $this->columns = Sabel_DB_SimpleCache::get('columns_' . $tblName);
      $properties    = Sabel_DB_SimpleCache::get('props_'   . $tblName);
    } else {
      $sClsName = 'Schema_' . $mdlName;
      Sabel::using($sClsName);

      if (class_exists($sClsName, false)) {
        list ($tblSchema, $properties) = $this->getSchemaFromCls($sClsName, $tblName);
      } else {
        list ($tblSchema, $properties) = $this->getSchemaFromDb($conName, $tblName);
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
  
  protected function getSchemaFromDb($conName, $tblName)
  {
    Sabel::using('Sabel_DB_Schema_Accessor');

    $scmName    = Sabel_DB_Connection::getSchema($conName);
    $database   = Sabel_DB_Connection::getDB($conName);
    $accessor   = new Sabel_DB_Schema_Accessor($conName, $scmName);
    $engine     = ($database === 'mysql') ? $accessor->getTableEngine($tblName) : null;
    $tblSchema  = $accessor->getTable($tblName);

    $properties = array('connectName'  => $conName,
                        'primaryKey'   => $tblSchema->getPrimaryKey(),
                        'incrementKey' => $tblSchema->getIncrementKey(),
                        'tableEngine'  => $engine,
                        'table'        => $tblName);

    return array($tblSchema, $properties);
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

    return array($tblSchema, $properties);
  }
  
  protected function createProperty($mdlName = null, $mdlProps = null)
  {
    $mdlName  = ($mdlName === null)  ? get_class($this) : $mdlName;
    $mdlProps = ($mdlProps === null) ? get_object_vars($this) : $mdlProps;
    
    $conName  = (isset($mdlProps['connectName'])) ? $mdlProps['connectName'] : 'default';
    $this->initSchema($mdlName, $conName);
    
    $this->property  = new Sabel_DB_Model_Property($mdlName, $mdlProps);
    $this->property->setColumns($this->columns);
  }

  public function __set($key, $val)
  {
    $this->property->$key = $val;
  }

  public function __get($key)
  {
    $value = $this->property->$key;
    return ($value === null) ? null : $this->convertData($key, $value);
  }
  
  public function convertData($key, $data)
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
          $data = ($data === 1);
        } elseif(is_string($data)) {
          $data = (in_array($data, array('1', 't', 'true')));
        }
    }
    return $data;
  }
  
  public function getRealData()
  {
    $cols = $this->columns;
    $data = $this->property->getData();
    $real = array();

    foreach ($data as $key => $val) {
      if (in_array($key, $cols)) $real[$key] = $this->convertData($key, $val);
    }
    return $real;
  }

  public function __clone()
  {
    $this->property = clone $this->property;
  }

  public function __call($method, $parameters)
  {
    if ($this->property === null) $this->createProperty();
    @list ($arg1, $arg2, $arg3) = $parameters;
    return $this->property->$method($arg1, $arg2, $arg3);
  }

  public function schema($tblName = null)
  {
    if (isset($tblName)) return $this->getTableSchema($tblName)->getColumns();
    
    $columns = $this->schema->getColumns();
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
    return ($tblName === null) ? $this->schema
                               : parent::getTableSchema($tblName);
  }

  public function getColumnNames($tblName = null)
  {
    return ($tblName === null) ? $this->columns
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
      $model->transrate(($withParent) ? $this->addParent($row) : $row);
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

      $model->transrate(($withParent) ? $this->addParent($row) : $row);
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
      $childObj->transrate(($withParent) ? $this->addParent($row) : $row);
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
      if ($this->validateOnUpdate) {
        if (($this->errors = $this->validate())) return false;
      }

      $saveData = ($data) ? $data : $this->property->getNewData();
      $this->recordTime($saveData, $tblName, self::UPDATE_TIME_COLUMN);
      $this->conditions = $this->property->getSelectCondition();
      $this->update($saveData);
      $this->property->unsetNewData();
      $newData = array_merge($this->getRealData(), $saveData);
    } else {
      if ($this->validateOnInsert) {
        if (($this->errors = $this->validate())) return false;
      }

      $driver  = $this->getDriver();
      $stmt    = $this->getStatement();
      $newData = ($data) ? $data : $this->property->getData();

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
    return (method_exists($this, 'validate' . ucfirst($name)));
  }

  protected function executeValidateMethod($name, $value)
  {
    $methodName = "validate" . ucfirst($name);
    return $this->$methodName($name, $value);
  }

  /**
   * validate with schema
   *
   */
  public function validate()
  {
    // instanciate schema
    $this->sColumns = $this->schema->getColumns();

    $this->errors    = $errors = Sabel::load('Sabel_Errors');
    $dataForValidate = $this->property->getValidateData();

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

  protected function getLocalizedName($name)
  {
    return (isset($this->localize[$name])) ? $this->localize[$name] : $name;
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
