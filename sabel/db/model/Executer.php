<?php

/**
 * Sabel_DB_Model_Executer
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model_Executer
{
  const INTERRUPTED_BY_DRIVER = -1;

  protected
    $model  = null,
    $driver = null,
    $method = "";

  protected
    $projection       = "*",
    $parents          = array(),
    $arguments        = array(),
    $constraints      = array(),
    $conditionManager = null,
    $incrementId      = null;

  public function __construct($model)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }

    $this->setModel($model);
  }

  public function setModel($model, $clearState = true)
  {
    if (!$model instanceof Sabel_DB_Model) {
      $name = get_class($model);
      throw new Exception("'{$name}' should be instance of Sabel_DB_Model.");
    }

    $this->model  = $model;
    $this->driver = Sabel_DB_Config::loadDriver($model->getConnectionName());

    if (!$this->driver instanceof Sabel_DB_Abstract_Driver) {
      $name = get_class($model);
      throw new Exception("'{$name}' should be instance of Sabel_DB_Abstract_Driver.");
    }

    if ($clearState) $this->clearState();

    return $this;
  }

  public function getModel()
  {
    return $this->model;
  }

  public function getDriver()
  {
    return $this->driver;
  }

  public function execute($clearState = true)
  {
    $method = "_" . $this->method;
    $result = $this->$method();

    if ($clearState) $this->clearState();

    return $result;
  }

  private function _execute($stmt)
  {
    $stmtType = $stmt->getStatementType();

    try {
      if (!$this->driverInterrupt("before", $stmtType)) {
        $result = $this->driver->setSql($stmt->getSql())->execute();
        $this->driverInterrupt("after", $stmtType);
      }
      return $result;
    } catch (Exception $e) {
      Sabel_DB_Transaction::rollback();
      throw new Sabel_DB_Exception($e->getMessage());
    }
  }

  public function clearState()
  {
    $this->unsetConditions(true);
    $this->arguments  = array();
    $this->projection = "*";
  }

  private function __call($method, $args)
  {
    if (substr($method, 0, 1) === "_") {
      // @todo
      // $ee = new ExtendedExecuter($this);
      // return $ee->$method();
      exit;
    } else {
      $this->method    = $method;
      $this->arguments = $args;

      return $this;
    }
  }

  public function getArguments()
  {
    return $this->arguments;
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
    if (is_string($parents)) {
      $parents = (array)$parents;
    }

    $this->parents = $parents;
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
      $manager->create($this->model->getPrimaryKey(), $arg1);
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
        $val = preg_replace_callback("/[^|,][^\.,]+\./", "_sc_cb_func", $val);
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

  public function getCount($arg1 = null, $arg2 = null)
  {
    $this->method    = "getCount";
    $this->arguments = array($arg1, $arg2);

    return $this;
  }

  protected function _getCount()
  {
    list ($arg1, $arg2) = $this->arguments;
    $this->setCondition($arg1, $arg2);

    $constraints = $this->constraints;
    $this->constraints = array("limit", 1);

    $sql  = "SELECT COUNT(*) AS cnt FROM " . $this->model->getTableName();
    $stmt = $this->createSelectStatement($sql);
    $rows = $this->_execute($stmt);

    $this->constraints = $constraints;

    return $rows[0]["cnt"];
  }

  public function selectOne($arg1 = null, $arg2 = null)
  {
    $this->method    = "selectOne";
    $this->arguments = array($arg1, $arg2);

    return $this;
  }

  protected function _selectOne()
  {
    list ($arg1, $arg2) = $this->arguments;

    if ($arg1 === null && $this->conditionManager === null) {
      throw new Sabel_DB_Exception("selectOne() must set the condition.");
    }

    $this->setCondition($arg1, $arg2);
    return $this->createModel($this->model);
  }

  private function createModel($model)
  {
    $stmt = $this->createSelectStatement();
    $rows = $this->_execute($stmt);

    if (isset($rows[0])) {
      $model->setProperties($rows[0]);
      if ($this->parents) $model->addParent($this->parents);
    } else {
      $manager = $this->loadConditionManager();
      $conditions = $manager->getConditions();

      foreach ($conditions as $condition) {
        if ($manager->isObject($condition)) {
          $model->__set($condition->key, $condition->value);
        }
      }
    }

    return $model;
  }

  public function select($arg1 = null, $arg2 = null)
  {
    $this->method    = "select";
    $this->arguments = array($arg1, $arg2);

    return $this;
  }

  protected function _select()
  {
    list ($arg1, $arg2) = $this->arguments;

    $this->setCondition($arg1, $arg2);
    $parents = $this->parents;

    if ($parents) {
      $result = $this->internalJoin();
      if ($result !== Sabel_DB_Join::CANNOT_JOIN) return $result;
    }

    $stmt = $this->createSelectStatement();
    $rows = $this->_execute($stmt);
    if (empty($rows)) return false;

    $results = array();
    $source  = MODEL($this->model->getModelName());

    foreach ($rows as $row) {
      $model = clone $source;
      $model->setProperties($row);

      if ($parents) $model->addParent($parents);
      $results[] = $model;
    }

    return $results;
  }

  private function internalJoin()
  {
    $join = new Sabel_DB_Join($this);

    if ($join->buildParents() === Sabel_DB_Join::CANNOT_JOIN) {
      return Sabel_DB_Join::CANNOT_JOIN;
    } else {
      return $join->join();
    }
  }

  private function addParent($parents)
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
    return $child->select($fkey, $this->__get($col));
  }

  public function save()
  {
    $this->method = "save";

    return $this;
  }

  protected function _save()
  {
    $model = $this->model;
    $mode  = ($model->isSelected()) ? "update" : "insert";

    if ($mode === "update") {
      if ($model->getPrimaryKey() === null) {
        $message = "save() cannot update model(there is not primary key).";
        throw new Sabel_DB_Exception($message);
      } else {
        $saveValues = $model->getUpdateValues();
      }
    } else {
      $saveValues = $model->toArray();
    }

    foreach ($saveValues as $key => $val) {
      $saveValues[$key] = $model->__get($key);
    }

    $model->setSaveValues($saveValues);
    $method = "create" . ucfirst($mode) . "Statement";
    $this->_execute($this->$method());

    if ($mode === "update") {
      $saveValues = array_merge($model->toArray(), $saveValues);
    } elseif (($incCol = $model->getIncrementColumn()) !== null) {
      $saveValues[$incCol] = $this->incrementId;
    }

    $newModel = MODEL($model->getModelName());
    $newModel->setProperties($saveValues);

    return $newModel;
  }

  public function insert($data = null)
  {
    $this->method    = "insert";
    $this->arguments = array($data);

    return $this;
  }

  protected function _insert()
  {
    list ($data) = $this->arguments;
    $this->model->setSaveValues($this->chooseValues($data, "insert"));
    $this->_execute($this->createInsertStatement());
    return $this->incrementId;
  }

  public function update($data = null)
  {
    $this->method    = "update";
    $this->arguments = array($data);

    return $this;
  }

  protected function _update($data = null)
  {
    list ($data) = $this->arguments;
    $conditions  = $this->loadConditionManager()->getConditions();
    $this->model->setSaveValues($this->chooseValues($data, "update"));
    $this->_execute($this->createUpdateStatement($conditions));
  }

  public function arrayInsert($data)
  {
    $this->method    = "arrayInsert";
    $this->arguments = array($data);

    return $this;
  }

  public function _arrayInsert()
  {
    list ($data) = $this->arguments;

    if (!is_array($data)) {
      $e = new Sabel_DB_Exception_Model();
      throw $e->missing("arrayInsert", $data);
    }

    Sabel_DB_Transaction::begin($this->model->getConnectionName());
    $this->model->setSaveValues($data);
    $this->_execute($this->createInsertStatement());
    Sabel_DB_Transaction::commit();
  }

  public function delete($arg1 = null, $arg2 = null)
  {
    $this->method    = "delete";
    $this->arguments = array($arg1, $arg2);

    return $this;
  }

  protected function _delete()
  {
    $model   = $this->model;
    $manager = $this->loadConditionManager();

    list ($arg1, $arg2) = $this->arguments;

    if (!$model->isSelected() && $arg1 === null && $manager->isEmpty()) {
      $message = "delete() must set the condition.";
      throw new Sabel_DB_Exception($message);
    }

    if ($arg1 !== null) {
      $this->setCondition($arg1, $arg2);
    } elseif ($model->isSelected()) {
      if (($pkey = $model->getPrimaryKey()) === null) {
        $message = "delete() cannot delete model(there is not primary key).";
        throw new Sabel_DB_Exception($message);
      } else {
        if (is_string($pkey)) $pkey = (array)$pkey;

        foreach ($pkey as $key) {
          $this->setCondition($key, $model->__get($key));
        }
      }
    }

    $stmt = $this->createDeleteStatement();
    $this->_execute($stmt);
  }

  public function query($sql, $inputs = null, $assoc = false)
  {
    $this->method    = "query";
    $this->arguments = array($sql, $inputs, $assoc);

    return $this;
  }

  protected function _query()
  {
    list ($sql, $inputs, $assoc) = $this->arguments;

    if (isset($inputs) && !is_array($inputs)) {
      $e = new Sabel_DB_Exception_Model();
      throw $e->missing("executeQuery", $inputs, "second");
    }

    $driver = $this->driver;

    if ($inputs !== null) {
      $sql = vsprintf($sql, $driver->escape($inputs));
    }

    $rows = $driver->setSql($sql)->execute();
    if (empty($rows)) return null;

    if ($assoc) {
      return $rows;
    } else {
      $results = array();
      foreach ($rows as $row) $results[] = (object)$row;
      return $results;
    }
  }

  public function createSelectStatement($sql = "")
  {
    return Sabel_DB_Sql_Statement_Loader::load("select")->create($this, $sql);
  }

  public function createInsertStatement()
  {
    return Sabel_DB_Sql_Statement_Loader::load("insert")->create($this);
  }

  public function createUpdateStatement($conditions = null)
  {
    return Sabel_DB_Sql_Statement_Loader::load("update")->create($this, $conditions);
  }

  public function createDeleteStatement()
  {
    return Sabel_DB_Sql_Statement_Loader::load("delete")->create($this);
  }

  private function chooseValues($data, $method)
  {
    if (isset($data) && !is_array($data)) {
      $e = new Sabel_DB_Exception_Model();
      throw $e->missing($method, $data);
    } else {
      return ($data === null) ? $this->model->toArray() : $data;
    }
  }

  public function setIncrementId($id)
  {
    $this->incrementId = $id;
  }

  private function driverInterrupt($type, $stmtType)
  {
    $driver = $this->driver;

    if ($type === "before") {
      $methods = $driver->getBeforeMethods();
    } else {
      $methods = $driver->getAfterMethods();
    }

    if (isset($methods["all"])) {
      $method = $methods["all"];
      $driver->$method($this);
    }

    if (isset($methods[$stmtType])) {
      $method = $methods[$stmtType];
      return ($driver->$method($this) === self::INTERRUPTED_BY_DRIVER);
    }
  }
}

function _sc_cb_func($matches)
{
  return convert_to_tablename(trim($matches[0]));
}
