<?php

/**
 * Sabel_DB_Model
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model
{
  protected
    $tableName = "",
    $columns   = array(),
    $schema    = null;

  protected
    $values       = array(),
    $updateValues = array(),
    $saveValues   = array();

  private
    $selected = false;

  private
    $cascadeStack = array();

  protected
    $projection  = "*",
    $constraints = array();

  protected
    $structure   = "normal",
    $localize    = array(),
    $parents     = array();

  protected
    $ignoreNothingPrimaryKey = false;

  protected
    $conditionManager = null,
    $connectionName   = "default";

  /*
  protected
    $validateMessages = array('length'   => 'is too long',
                              'maximum'  => 'is too large',
                              'nullable' => 'should not be a blank',
                              'type'     => 'invalid data type');
  */

  public function __construct($arg1 = null, $arg2 = null)
  {
    $this->initialize();
    if (!empty($arg1)) $this->initializeSelect($arg1, $arg2);
  }

  protected function initialize($mdlName = null)
  {
    if ($mdlName === null) $mdlName = get_class($this);

    if ($this->tableName === "") {
      $tblName = convert_to_tablename($mdlName);
      $this->tableName = $tblName;
    } else {
      $tblName = $this->tableName;
    }

    $this->schema  = $schema = Sabel_DB_Schema_Loader::getSchema($this);
    $this->columns = $schema->getColumnNames();

    if ($schema->getPrimaryKey() === null) {
      if (!$this->ignoreNothingPrimaryKey && $this->structure !== "view") {
        trigger_error("primary key not found in $tblName", E_USER_NOTICE);
      }
    }
  }

  protected function initializeSelect($arg1, $arg2 = null)
  {
    $this->setCondition($arg1, $arg2);
    $this->createModel($this);
  }

  public function setConnectionName($connectionName)
  {
    $this->connectionName = $connectionName;
  }

  public function getConnectionName()
  {
    return $this->connectionName;
  }

  public function getCommand()
  {
    return new Sabel_DB_Command_Executer($this);

    // @todo for mssql
    // $driver->extension($this->tableProp);
  }

  public function __set($key, $val)
  {
    $this->values[$key] = $val;

    if ($this->selected && in_array($key, $this->columns)) {
      $this->updateValues[$key] = $val;
    }
  }

  public function setTableName($tblName)
  {
    $this->tableName = $tblName;
  }

  public function getTableName()
  {
    return $this->tableName;
  }

  public function getColumnNames()
  {
    return $this->columns;
  }

  public function getPrimaryKey()
  {
    return $this->schema->getPrimaryKey();
  }

  public function getIncrementColumn()
  {
    return $this->schema->getIncrementColumn();
  }

  public function getTableEngine()
  {
    return $this->schema->getTableEngine();
  }

  public function getSchema()
  {
    return $this->schema;
  }

  public function setProjection($p)
  {
    $this->projection = (is_array($p)) ? join(", ", $p) : $p;
  }

  public function getProjection()
  {
    return $this->projection;
  }

  public function setParents($parents)
  {
    if (!is_array($parents)) {
      throw new Exception("argument should be an array.");
    }

    $this->parents = $parents;
  }

  public function loadConditionManager()
  {
    if ($this->conditionManager === null) {
      return $this->conditionManager = new Sabel_DB_Condition_Manager();
    } else {
      return $this->conditionManager;
    }
  }

  public function setConditionManager($manager)
  {
    $this->conditionManager = $manager;
  }

  public function setCondition($arg1, $arg2 = null, $arg3 = null)
  {
    if (empty($arg1)) return null;

    $manager = $this->loadConditionManager();

    if (is_array($arg1)) {
      $manager->create($arg1);
    } elseif ($arg1 instanceof Sabel_DB_Condition_Object) {
      $manager->add($arg1);
    } else {
      if ($arg2 === null) {
        $arg2 = $arg1;
        $arg1 = $this->getPrimaryKey();
      }
      $manager->create($arg1, $arg2, $arg3);
    }
  }

  public function getConditionManager()
  {
    return $this->conditionManager;
  }

  public function setConstraint($arg1, $arg2 = null)
  {
    if (!is_array($arg1)) $arg1 = array($arg1 => $arg2);

    foreach ($arg1 as $key => $val) {
      if (strpos($val, ".") !== false) {
        list($mdlName, $val) = explode(".", $val);
        $val = convert_to_tablename($mdlName) . "." . $val;
      }

      $this->constraints[$key] = $val;
    }
  }

  public function getConstraints()
  {
    return $this->constraints;
  }

  public function unsetConditions($with = false)
  {
    if ($this->conditionManager !== null) {
      $this->conditionManager->clear();
    }

    if ($with) $this->unsetConstraints();
  }

  public function unsetConstraints()
  {
    $this->constraints = array();
  }

  public function __get($key)
  {
    if (!isset($this->values[$key])) return null;
    return $this->convertData($key, $this->values[$key]);
  }

  protected function convertData($key, $data)
  {
    if ($data === null) return null;

    $schema = $this->schema->getColumns();
    if (!isset($schema[$key])) return $data;

    switch ($schema[$key]->type) {
      case Sabel_DB_Type_Const::INT:
        return ($data > 2147483647) ? (float)$data : (int)$data;
      case Sabel_DB_Type_Const::FLOAT:
      case Sabel_DB_Type_Const::DOUBLE:
        return (float)$data;
      case SabeL_DB_Type_Const::BOOL:
        if (is_string($data)) {
          return in_array($data, array('1', 't', 'true', __TRUE__));
        } elseif (is_bool($data)) {
          return $data;
        } elseif (is_int($data)) {
          return ($data === 1);
        }
      case Sabel_DB_Type_Const::DATETIME:
        return (is_object($data)) ? $data : Sabel::load('Sabel_Date', $data);
      default:
        return $data;
    }
  }

  public function getRealData()
  {
    $real = array();
    foreach ($this->values as $key => $val) {
      if (in_array($key, $this->columns)) $real[$key] = $this->convertData($key, $val);
    }
    return $real;
  }

  public function toArray()
  {
    return $this->values;
  }

  public function getLocalizedName($name)
  {
    return (isset($this->localize[$name])) ? $this->localize[$name] : $name;
  }

  public function getStructure()
  {
    return $this->structure;
  }

  public function getParents()
  {
    return $this->parents;
  }

  public function isSelected()
  {
    return $this->selected;
  }

  /*
  public function addValidateIgnore($cols)
  {
    if (is_string($cols)) $cols = (array)$cols;
    foreach ($cols as $col) $this->validateIgnores[] = $col;
  }
  */

  public function toSchema()
  {
    $columns = $this->schema->getColumns();

    foreach ($this->values as $name => $value) {
      if (isset($columns[$name])) {
        $columns[$name]->value = $this->convertData($name, $value);
      }
    }

    return $columns;
  }

  public function begin()
  {
    $this->getCommand()->begin($this->getConnectionName());
  }

  public function startTransaction()
  {
    $this->begin();
  }

  public function addTransaction()
  {
    $this->begin();
  }

  public function commit()
  {
    $this->getCommand()->commit();
  }

  public function rollback()
  {
    $this->getCommand()->rollback();
  }

  public function close()
  {
    Sabel_DB_Connection::close($this->getConnectName());
  }

  public function getFirst($orderColumn)
  {
    return $this->getEdge("ASC", $orderColumn);
  }

  public function getLast($orderColumn)
  {
    return $this->getEdge("DESC", $orderColumn);
  }

  protected function getEdge($order, $orderColumn)
  {
    $this->setCondition($orderColumn, Sabel_DB_Condition_Object::NOTNULL);

    $c =& $this->constraints;
    $tmpLimit = (isset($c["limit"])) ? $c["limit"] : null;
    $tmpOrder = (isset($c["order"])) ? $c["order"] : null;

    $c["limit"] = 1;
    $c["order"] = $orderColumn . " " . $order;

    $result = $this->selectOne();

    if ($tmpLimit !== null) $c["limit"] = $tmpLimit;
    if ($tmpOrder !== null) $c["order"] = $tmpOrder;

    return $result;
  }

  public function getCount($arg1 = null, $arg2 = null, $arg3 = null)
  {
    $this->setCondition($arg1, $arg2, $arg3);

    $tmpProjection  = $this->projection;
    $tmpConstraints = $this->constraints;
    $this->unsetConstraints();

    $this->setProjection("count(*)");
    $this->setConstraint("limit", 1);

    $row = $this->doSelect()->fetch(Sabel_DB_Result_Row::NUM);

    $this->projection  = $tmpProjection;
    $this->constraints = $tmpConstraints;

    return (int)$row[0];
  }

  public function selectOne($arg1 = null, $arg2 = null, $arg3 = null)
  {
    if ($arg1 === null && $this->conditionManager === null) {
      throw new Exception("must be set condition ( where ).");
    }

    $this->setCondition($arg1, $arg2, $arg3);
    return $this->createModel(clone $this);
  }

  protected function createModel($model)
  {
    if ($row = $model->doSelect()->fetch()) {
      $model->setProperties($row);
      if ($this->parents) $model->addParent($this->parents);
    } else {
      $manager = $model->loadConditionManager();
      $conditions = $manager->getConditions();

      foreach ($conditions as $condition) {
        if ($manager->addUnique($condition)) {
          $model->{$condition->key} = $condition->value;
        }
      }
    }

    return $model;
  }

  public function doSelect()
  {
    try {
      $command = $this->getCommand();
      return $command->select()->getResult();
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $command);
    }
  }

  public function select($arg1 = null, $arg2 = null, $arg3 = null)
  {
    $this->setCondition($arg1, $arg2, $arg3);

    $tblName = $this->getTableName();
    $parents = $this->parents;

    if ($parents) {
      $result = $this->internalJoin();
      if ($result !== Sabel_DB_Relation_Joiner::CANNOT_JOIN) return $result;
    }

    $resultSet = $this->doSelect();
    if ($resultSet->isEmpty()) return false;

    $results   = array();
    $modelName = convert_to_modelname($tblName);

    $rows = $resultSet->fetchAll();
    foreach ($rows as $row) {
      $model = MODEL($modelName);
      $model->setProperties($row);

      if ($parents) $model->addParent($parents);
      $results[] = $model;
    }

    return $results;
  }

  protected function internalJoin()
  {
    $joiner = new Sabel_DB_Relation_Joiner($this);
    $result = $joiner->buildParents();

    if ($result === Sabel_DB_Relation_Joiner::CANNOT_JOIN) {
      return Sabel_DB_Relation_Joiner::CANNOT_JOIN;
    } else {
      return $joiner->join();
    }
  }

  protected function addParent($parents)
  {
    $counterfeit = new Sabel_DB_Relation_Join_Counterfeit($this);
    $counterfeit->setParents($parents);
  }

  public function getChild($childName, $constraints = null)
  {
    $keys = Sabel_DB_Relation_Key::create($this, null);
    $id   = $this->$keys["id"];

    $child = MODEL($childName);

    if ($constraints) {
      $child->setConstraint($constraints);
    }

    return $child->select($keys["fKey"], $id);
  }

  public function setProperties($row)
  {
    $pKey = $this->getPrimaryKey();
    if (!is_array($pKey)) $pKey = (array)$pKey;

    $manager = $this->loadConditionManager();

    foreach ($pKey as $key) {
      if (isset($row[$key])) {
        $manager->addUnique(new Sabel_DB_Condition_Object($key, $row[$key]));
      }
    }

    foreach ($row as $key => $val) {
      $this->values[$key] = $val;
    }

    $this->selected = true;
  }

  public function save($data = null)
  {
    if (isset($data) && !is_array($data)) {
      throw new Exception("argument must be an array");
    }

    if ($this->isSelected()) {
      $saveValues = ($data) ? $data : $this->updateValues;
      $this->updateValues= array();
    } else {
      $saveValues = ($data) ? $data : $this->values;
    }

    $this->saveValues = $saveValues;

    $command  = $this->getCommand();
    $tblName  = $this->getTableName();
    $newModel = MODEL(convert_to_modelname($tblName));

    if ($this->isSelected()) {
      $command->update();
      $saveValues = array_merge($this->toArray(), $saveValues);
    } else {
      $incCol = $this->getIncrementColumn();

      try {
        $id = $command->insert()->getIncrementId();
      } catch (Exception $e) {
        $this->executeError($e->getMessage(), $command);
      }

      if (($incCol = $this->getIncrementColumn()) !== null) {
        if (!isset($saveValues[$incCol])) $saveValues[$incCol] = $id;
      }
    }

    $newModel->setProperties($saveValues);
    $this->saveValues = array();
    return $newModel;
  }

  public function setSaveValues($values)
  {
    if (!is_array($values)) {
      throw new Exception("argument must be an array");
    }

    return $this->saveValues = $values;
  }

  public function getSaveValues()
  {
    return $this->saveValues;
  }

  public function doUpdate($data)
  {
    try {
      $command = $this->getCommand();
      $command->update($this);
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $command);
    }
  }

  public function validate()
  {
    $this->sColumns = $this->schema->getColumns();

    if (is_object($this->errors)) {
      $errors = $this->errors;
    } else {
      $this->errors = $errors = new Sabel_Errors();
    }

    $dataForValidate = ($this->isSelected()) ? $this->newData : $this->values;

    foreach ($dataForValidate as $name => $value) {
      if (in_array($name, $this->validateIgnores)) continue;
      $lname = $this->getLocalizedName($name);
      if ($this->validateLength($name, $value)) {
        $errors->add($lname, $this->validateMessages["length"]);
      } elseif ($this->validateMaximum($name, $value)) {
        $errors->add($lname, $this->validateMessages["maximum"]);
      } elseif ($this->validateNullable($name, $value)) {
        $errors->add($lname, $this->validateMessages["nullable"]);
      } elseif ($this->validateType($name, $value)) {
        $errors->add($lname, $this->validateMessages["type"]);
      } elseif ($this->hasValidateMethod($name)) {
        $this->executeValidateMethod($name, $value);
      }
    }

    $nonInputs = $this->validateNonInputs($dataForValidate);
    foreach ($nonInputs as $name) {
      $name = $this->getLocalizedName($name);
      $errors->add($name, $this->validateMessages["nullable"]);
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
    $col = $this->sColumns[$name];
    if ($col->type === Sabel_DB_Type_Const::STRING) {
      $method = (extension_loaded('mbstring')) ? 'mb_strwidth' : 'strlen';
      return ($method($value) > $col->max);
    } else {
      return false;
    }
  }

  protected function validateMaximum($name, $value)
  {
    $col = $this->sColumns[$name];
    return ($col->type === Sabel_DB_Type_Const::INT && $col->max < $value);
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
        if ($value === null || is_int($value)) return false;
        if (is_string($value)) return !preg_match('/^[-|+]?[0-9]+$/', $value);
        return true;
        break;
      case Sabel_DB_Type_Const::BOOL:
        if ($value === null || $value === '') return false;
        return ($value !== __TRUE__ && $value !== __FALSE__);
        break;
      case Sabel_DB_Type_Const::DATETIME:
        return !((boolean) strtotime($value));
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

  public function arrayInsert($data)
  {
    if (!is_array($data)) {
      throw new Exception("arrayInsert() argument is not array.");
    }

    $command = $this->getCommand();
    $this->saveValues = $data;

    try {
      $command->begin();
      $command->arrayInsert();
      $command->commit();
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  public function remove($arg1 = null, $arg2 = null, $arg3 = null)
  {
    $manager = $this->loadConditionManager();

    if ($arg1 === null) {
      if ($manager->isEmpty() && !$this->isSelected()) {
        throw new Exception("All Delete? must be set condition.");
      }
    } elseif ($this->structure === "view") {
      throw new Exception("delete command cannot execute to view.");
    }

    if ($this->isSelected()) {
      $pKey  = $this->getPrimaryKey();
      $ucond = $this->conditionManager->getUniqueConditions();

      if (!is_array($pKey)) $pKey = (array)$pKey;

      foreach ($pKey as $key) {
        $this->setCondition($key, $ucond[$key]->value);
      }
    } else {
      $this->setCondition($arg1, $arg2, $arg3);
    }

    $command = $this->getCommand();
    $command->delete();
  }

  public function cascadeDelete($id = null)
  {
    if (!class_exists('Schema_CascadeChain', true))
      throw new Exception('Error: class Schema_CascadeChain does not exist.');

    if ($id === null && !$this->isSelected())
      throw new Exception('Error: give the value of id or select the model beforehand.');

    $chain   = Schema_CascadeChain::get();
    $tblName = $this->getTableName();

    if (isset($chain[$tblName])) {
      $tables = $chain[$tblName];
    } else {
      throw new Exception("Error: cascadeDelete() '{$tblName}' does not exist in the cascade chain.");
    }

    $this->begin();

    $models = array();
    $pKey   = $this->getPrimaryKey();
    foreach ($tables as $table) {
      list ($table, $foreignKey, $idCol) = $this->createCascadeParam($table, $tblName, $pKey);

      $idValue = (isset($id)) ? $id : $this->$idCol;
      if ($model = $this->pushStack($table, $foreignKey, $idValue)) $models[] = $model;
    }

    foreach ($models as $children) $this->makeChainModels($children, $chain);

    $this->clearCascadeStack(array_reverse($this->cascadeStack));
    $this->remove($pKey, $id);

    $this->commit();
  }

  private function makeChainModels($children, &$chain)
  {
    $tblName = $children[0]->getTableName();
    if (isset($chain[$tblName])) {
      $tables = $chain[$tblName];
    } else {
      return null;
    }

    $models = array();
    foreach ($tables as $table) {
      foreach ($children as $child) {
        $tblName = $child->getTableName();
        $pKey    = $child->getPrimaryKey();
        list ($table, $foreignKey, $idCol) = $this->createCascadeParam($table, $tblName, $pKey);
        if ($model = $this->pushStack($table, $foreignKey, $child->$idCol)) $models[] = $model;
      }
    }

    if ($models) {
      foreach ($models as $children) $this->makeChainModels($children, $chain);
    }
  }

  private function pushStack($tblName, $foreignKey, $id)
  {
    $model  = MODEL(convert_to_modelname($tblName));
    $model->setParents(array());
    $models = $model->select($foreignKey, $id);

    if ($models) $this->cascadeStack["{$tblName}:{$id}"] = $foreignKey;
    return $models;
  }

  private function createCascadeParam($chainValue, $tblName, $primaryKey)
  {
    if (strpos($chainValue, ':') === false) {
      $idCol = $primaryKey;
    } else {
      list ($idCol, $chainValue) = explode(':', $chainValue);
    }

    if (strpos($chainValue, '.') === false) {
      $foreignKey = "{$tblName}_{$primaryKey}";
    } else {
      list ($chainValue, $foreignKey) = explode('.', $chainValue);
    }

    return array($chainValue, $foreignKey, $idCol);
  }

  private function clearCascadeStack($stack)
  {
    foreach ($stack as $param => $foreignKey) {
      list($tName, $idValue) = explode(':', $param);
      $model = MODEL(convert_to_modelname($tName));

      $model->begin();
      $model->remove($foreignKey, $idValue);
    }
  }

  public function executeQuery($sql, $inputs = null)
  {
    if (isset($inputs) && !is_array($inputs)) {
      throw new Exception("second argument must be an array");
    }

    $command = $this->getCommand();

    try {
      $command->query($sql, $inputs);
      $resultSet = $command->getResult();
      if ($resultSet->isEmpty()) return false;

      $models  = array();
      $tblName = $this->getTableName();
      $mdlName = convert_to_modelname($tblName);

      foreach ($resultSet as $row) {
        $model = MODEL($mdlName);
        foreach ($row as $key => $val) {
          $model->values[$key] = $val;
        }

        $models[] = $model;
      }

      return $models;
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $command);
    }
  }

  protected function executeError($errorMsg, $command)
  {
    $command->rollback();
    throw new Exception($errorMsg);
  }

  /**
   * an alias for setCondition()
   *
   * @return void
   */
  public function scond($arg1, $arg2 = null, $not = null)
  {
    $this->setCondition($arg1, $arg2, $not);
  }

  /**
   * an alias for setConstraint()
   *
   * @return void
   */
  public function sconst($arg1, $arg2 = null)
  {
    $this->setConstraint($arg1, $arg2);
  }
}
