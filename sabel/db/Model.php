<?php

/**
 * Sabel_DB_Model
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Model
{
  protected
    $connectionName = "default";

  protected
    $tableName  = "",
    $modelName  = "",
    $schema     = null,
    $columns    = array(),
    $schemaCols = array(),
    $selected   = false,
    $projection = "*",
    $parents    = array();

  protected
    $values       = array(),
    $updateValues = array(),
    $saveValues   = array();

  protected
    $constraints      = array(),
    $conditionManager = null;

  public function __construct($arg1 = null, $arg2 = null)
  {
    $this->initialize();
    if (!empty($arg1)) $this->initializeSelect($arg1, $arg2);
  }

  protected function initialize($mdlName = null)
  {
    if ($mdlName === null) $mdlName = get_class($this);
    $this->modelName = $mdlName;

    if ($this->tableName === "") {
      $this->tableName = convert_to_tablename($mdlName);
    }

    $this->schema = $schema = Sabel_DB_Schema_Loader::getSchema($this);
    $this->schemaCols = $columns = $schema->getColumns();
    $this->columns = array_keys($columns);
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
  }

  public function __call($method, $args)
  {
    $command = $this->getCommand();

    try {
      if (empty($args)) {
        return $command->$method()->getResult();
      } else {
        $code = $this->getEvalCode(count($args));
        eval ('$result = ' . $code);
        return $result;
      }
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $command);
    }
  }

  public function __set($key, $val)
  {
    $this->values[$key] = $val;

    if ($this->selected && in_array($key, $this->columns)) {
      $this->updateValues[$key] = $val;
    }
  }

  public function setValues($values)
  {
    foreach ($values as $key => $val) {
      $this->__set($key, $val);
    }
  }

  public function __get($key)
  {
    if (!isset($this->values[$key])) return null;

    $value = $this->values[$key];
    if ($value === null) return null;

    $columns = $this->schemaCols;
    if (!isset($columns[$key])) return $value;
    return $columns[$key]->cast($value);
  }

  public function setTableName($tblName)
  {
    $this->tableName = $tblName;
  }

  public function getTableName()
  {
    return $this->tableName;
  }

  public function getModelName()
  {
    return $this->modelName;
  }

  public function getColumnNames()
  {
    return $this->columns;
  }

  public function getSchema()
  {
    return $this->schema;
  }

  public function getPrimaryKey()
  {
    return $this->schema->getPrimaryKey();
  }

  public function getIncrementColumn()
  {
    return $this->schema->getIncrementColumn();
  }

  public function setSaveValues($values)
  {
    if (is_array($values)) {
      return $this->saveValues = $values;
    } else {
      $e = new Sabel_DB_Exception_Model();
      throw $e->missing("setSaveValues", $values);
    }
  }

  public function getSaveValues()
  {
    return $this->saveValues;
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
    if (is_array($parents)) {
      $this->parents = $parents;
    } else {
      $e = new Sabel_DB_Exception_Model();
      throw $e->missing("setParents", $parents);
    }
  }

  public function getParents()
  {
    return $this->parents;
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

  public function setCondition($arg1, $arg2 = null)
  {
    if (empty($arg1)) return null;

    $manager = $this->loadConditionManager();

    if (is_array($arg1)) {
      $manager->create($arg1);
    } elseif ($arg1 instanceof Sabel_DB_Condition_Object) {
      $manager->add($arg1);
    } elseif ($arg2 === null) {
      $manager->create($this->getPrimaryKey(), $arg1);
    } else {
      $manager->create($arg1, $arg2);
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
        $val = preg_replace_callback("/[^|,][^\.,]+\./", '_sc_cb_func', $val);
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

  public function toArray()
  {
    return $this->values;
  }

  public function toSchema()
  {
    $schemas = array();
    $values  = $this->values;

    foreach ($this->schemaCols as $name => $schema) {
      $cloned = clone $schema;
      if (isset($values[$name])) {
        $cloned->value = $cloned->cast($values[$name]);
      } else {
        $cloned->value = null;
      }

      $schemas[$name] = $cloned;
    }

    return $schemas;
  }

  public function isSelected()
  {
    return $this->selected;
  }

  public function getCount($arg1 = null, $arg2 = null)
  {
    $this->setCondition($arg1, $arg2);

    try {
      $command = $this->getCommand();
      return $command->count()->getResult();
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $command);
    }
  }

  public function selectOne($arg1 = null, $arg2 = null)
  {
    if ($arg1 === null && $this->conditionManager === null) {
      $e = new Sabel_DB_Exception_Model();
      throw $e->exception("selectOne", "must set the condition.");
    }

    $this->setCondition($arg1, $arg2);
    return $this->createModel(clone $this);
  }

  protected function createModel($model)
  {
    $command = $model->getCommand();
    $rows = $this->execSelect($command);

    if (isset($rows[0])) {
      $model->setProperties($rows[0]);
      if ($this->parents) $model->addParent($this->parents);
    } else {
      $manager = $model->loadConditionManager();
      $conditions = $manager->getConditions();

      foreach ($conditions as $condition) {
        if ($manager->isObject($condition)) {
          $model->{$condition->key} = $condition->value;
        }
      }
    }

    return $model;
  }

  public function select($arg1 = null, $arg2 = null)
  {
    $this->setCondition($arg1, $arg2);
    $parents = $this->parents;

    if ($parents) {
      $result = $this->internalJoin();
      if ($result !== Sabel_DB_Join::CANNOT_JOIN) return $result;
    }

    $command = $this->getCommand();
    $rows = $this->execSelect($command);
    if (empty($rows)) return false;

    $results   = array();
    $modelName = $this->getModelName();

    $source = MODEL($modelName);

    foreach ($rows as $row) {
      $model = clone $source;
      $model->setProperties($row);

      if ($parents) $model->addParent($parents);
      $results[] = $model;
    }

    return $results;
  }

  protected function internalJoin()
  {
    $join = new Sabel_DB_Join($this);

    if ($join->buildParents() === Sabel_DB_Join::CANNOT_JOIN) {
      return Sabel_DB_Join::CANNOT_JOIN;
    } else {
      return $join->join();
    }
  }

  protected function addParent($parents)
  {
    $counterfeit = new Sabel_DB_Join_Counterfeit($this);
    $counterfeit->setParents($parents);
  }

  public function getChild($childName, $constraints = null)
  {
    $child   = MODEL($childName);
    $foreign = $child->getSchema()->getForeignKeys();

    if ($foreign === null) {
      $col  = "id";
      $fkey = $this->getTableName() . "_id";
    } else {
      $tblName = $this->tableName;
      foreach ($foreign as $fkey => $params) {
        if ($params["referenced_table"] === $tblName) {
          $col = $params["referenced_column"];
          break;
        }
      }
    }

    if ($constraints) $child->setConstraint($constraints);
    return $child->select($fkey, $this->$col);
  }

  public function setProperties($row)
  {
    $pkey = $this->getPrimaryKey();
    if (!is_array($pkey)) $pkey = (array)$pkey;

    $manager  = $this->loadConditionManager();
    $selected = true;

    foreach ($pkey as $key) {
      if (isset($row[$key])) {
        $c = new Sabel_DB_Condition_Object($key, $row[$key]);
        $manager->addUnique($c);
      } else {
        $selected = false;
      }
    }

    $this->values   = $row;
    $this->selected = $selected;
  }

  public function validate($ignores = array())
  {
    $validator = new Sabel_DB_Validator($this);
    return $validator->validate($ignores);
  }

  public function save($ignores = null)
  {
    if ($ignores) {
      if ($ignores === true) $ignores = array();
      $errors = $this->validate($ignores);

      if ($errors) {
        $stdClass = new stdClass();
        $stdClass->hasError = true;
        $stdClass->errors = $errors;
        return $stdClass;
      }
    }

    if ($this->isSelected()) {
      if ($this->getPrimaryKey() === null) {
        $e = new Sabel_DB_Exception_Model();
        throw $e->exception("save", "cannot update model(there is not primary key).");
      } else {
        $saveValues = $this->updateValues;
        $saveMethod = "update";
        $this->updateValues = array();
      }
    } else {
      $saveValues = $this->values;
      $saveMethod = "insert";
    }

    $this->saveValues = $saveValues;

    try {
      $command = $this->getCommand();
      $command->$saveMethod();
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $command);
    }

    $this->saveValues = array();

    if ($this->isSelected()) {
      $saveValues = array_merge($this->toArray(), $saveValues);
    } elseif (($incCol = $this->getIncrementColumn()) !== null) {
      $saveValues[$incCol] = $command->getIncrementId();
    }

    $newModel = MODEL($this->getModelName());
    $newModel->setProperties($saveValues);

    return $newModel;
  }

  public function insert($data = null)
  {
    $this->saveValues = $this->chooseValues($data, "insert");

    try {
      $command = $this->getCommand();
      $command->insert()->getResult();
      return $command->getIncrementId();
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $command);
    }
  }

  public function update($data = null)
  {
    $this->saveValues = $this->chooseValues($data, "update");

    try {
      $command = $this->getCommand();
      $command->update($this->loadConditionManager()->getConditions());
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $command);
    }
  }

  public function arrayInsert($data)
  {
    if (is_array($data)) {
      $this->saveValues = $data;
    } else {
      $e = new Sabel_DB_Exception_Model();
      throw $e->missing("arrayInsert", $data);
    }

    try {
      Sabel_DB_Transaction::begin($this);
      $command = $this->getCommand();
      $command->arrayInsert();
      Sabel_DB_Transaction::commit();
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  public function delete($arg1 = null, $arg2 = null)
  {
    $manager = $this->loadConditionManager();

    if (!$this->isSelected() && $arg1 === null && $manager->isEmpty()) {
      $e = new Sabel_DB_Exception_Model();
      throw $e->exception("remove", "delete all? must set the condition.");
    }

    if ($arg1 !== null) {
      $this->setCondition($arg1, $arg2);
    } elseif ($this->isSelected()) {
      if (($pkey = $this->getPrimaryKey()) === null) {
        $e = new Sabel_DB_Exception_Model();
        throw $e->exception("save", "cannot delete model(there is not primary key).");
      } else {
        $ucond = $this->conditionManager->getUniqueConditions();
        if (is_string($pkey)) $pkey = (array)$pkey;

        foreach ($pkey as $key) {
          $this->setCondition($key, $ucond[$key]->value);
        }
      }
    }

    $this->getCommand()->delete();
  }

  public function executeQuery($sql, $inputs = null)
  {
    if (isset($inputs) && !is_array($inputs)) {
      $e = new Sabel_DB_Exception_Model();
      throw $e->missing("executeQuery", $inputs, "second");
    }

    try {
      $command = $this->getCommand();
      $rows = $command->query($sql, $inputs)->getResult();
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $command);
    }

    if (empty($rows)) return null;

    $results = array();
    foreach ($rows as $row) $results[] = (object)$row;

    return $results;
  }

  protected function execSelect($command)
  {
    try {
      return $command->select()->getResult();
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $command);
    }
  }

  private function chooseValues($data, $method)
  {
    if (isset($data) && !is_array($data)) {
      $e = new Sabel_DB_Exception_Model();
      throw $e->missing($method, $data);
    } else {
      return ($data === null) ? $this->values : $data;
    }
  }

  private final function getEvalCode($argsCount)
  {
    $args = array();
    for ($i = 0; $i < $argsCount; $i++) {
      $args[] = '$args[' . $i . ']';
    }

    $args = implode(", ", $args);
    return '$command->$method(' . $args . ')->getResult();';
  }

  private final function executeError($message, $command)
  {
    Sabel_DB_Transaction::rollback();
    throw new Sabel_DB_Exception($message);
  }
}

function _sc_cb_func($matches)
{
  return convert_to_tablename(trim($matches[0]));
}
