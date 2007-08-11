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

  public function setModel(Sabel_DB_Model $model, $clearState = true)
  {
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

  public function getArguments()
  {
    return $this->arguments;
  }

  public function before($method)
  {
    return null;
  }

  public function after($method)
  {
    return null;
  }

  public function executeStatement($stmt)
  {
    return $this->_execute($stmt);
  }

  public final function execute($clearState = true)
  {
    $method = $this->method;
    $result = $this->before($method);

    if ($result === null) {
      $execMethod = "_" . $method;
      $result = $this->$execMethod();
    }

    $afterResult = $this->after($method, $result);
    if ($afterResult !== null) $result = $afterResult;

    if ($clearState) $this->clearState();

    return $result;
  }

  private final function _execute(Sabel_DB_Abstract_Statement $stmt)
  {
    $stmtType = $stmt->getStatementType();

    try {
      $result = $this->driverInterrupt("before", $stmtType);
      if ($result === null) {
        $result = $this->driver->setSql($stmt->getSql())->execute();
      }

      $afterResult = $this->driverInterrupt("after", $stmtType);
      return ($afterResult === null) ? $result : $afterResult;
    } catch (Exception $e) {
      Sabel_DB_Transaction::rollback();
      throw new Sabel_DB_Exception($e->getMessage());
    }
  }

  public function clearState()
  {
    $this->unsetConditions(true);

    $this->projection = "*";
    $this->arguments  = array();
    $this->parents    = array();
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

  public function setConditionManager(Sabel_DB_Condition_Manager $manager)
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

    $projection = $this->projection;
    $this->projection = "COUNT(*) AS cnt";

    $sql  = $this->createSelectSql();
    $sql .= $this->createConditionSql();
    $sql  = $this->createConstraintSql($sql, array("limit" => 1));
    $stmt = Sabel_DB_Statement::create(Sabel_DB_Statement::SELECT);
    $rows = $this->_execute($stmt->setSql($sql));

    $this->projection = $projection;

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

  protected function createModel($model)
  {
    $sql  = $this->createSelectSql();
    $sql .= $this->createConditionSql();
    $sql  = $this->createConstraintSql($sql);
    $stmt = Sabel_DB_Statement::create(Sabel_DB_Statement::SELECT);
    $rows = $this->_execute($stmt->setSql($sql));

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

    $sql  = $this->createSelectSql();
    $sql .= $this->createConditionSql();
    $sql  = $this->createConstraintSql($sql);
    $stmt = Sabel_DB_Statement::create(Sabel_DB_Statement::SELECT);
    $rows = $this->_execute($stmt->setSql($sql));

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
    return $child->select($fkey, $this->__get($col));
  }

  public function save()
  {
    $this->method = "save";

    return $this;
  }

  protected function _save()
  {
    if ($this->model->isSelected()) {
      $saveValues = $this->_saveUpdate();
    } else {
      $saveValues = $this->_saveInsert();
    }

    $model = MODEL($this->model->getModelName());
    $model->setProperties($saveValues);

    return $model;
  }

  protected function _saveInsert()
  {
    $model      = $this->model;
    $saveValues = $model->toArray();

    foreach ($saveValues as $key => $val) {
      $saveValues[$key] = $model->__get($key);
    }

    $model->setSaveValues($saveValues);

    $driver = $this->driver;
    $stmt   = Sabel_DB_Statement::create(Sabel_DB_Statement::INSERT);
    $sql    = $driver->loadSqlClass($model)->buildInsertSql($driver);

    $this->_execute($stmt->setSql($sql));

    if (($incCol = $model->getIncrementColumn()) !== null) {
      $saveValues[$incCol] = $this->incrementId;
    }

    return $saveValues;
  }

  protected function _saveUpdate()
  {
    $model = $this->model;

    if (($pkey = $model->getPrimaryKey()) === null) {
      $message = "save() cannot update model(there is not primary key).";
      throw new Sabel_DB_Exception($message);
    } else {
      if (is_string($pkey)) $pkey = (array)$pkey;

      foreach ($pkey as $key) {
        $this->setCondition($key, $model->__get($key));
      }
    }

    $saveValues = $model->getUpdateValues();

    foreach ($saveValues as $key => $val) {
      $saveValues[$key] = $model->__get($key);
    }

    $model->setSaveValues($saveValues);

    $driver = $this->driver;
    $stmt   = Sabel_DB_Statement::create(Sabel_DB_Statement::UPDATE);
    $sql    = $driver->loadSqlClass($this->model)->buildUpdateSql($driver);
    $sql   .= $this->createConditionSql();

    $this->_execute($stmt->setSql($sql));

    return array_merge($model->toArray(), $saveValues);
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
    $stmt = Sabel_DB_Statement::create(Sabel_DB_Statement::INSERT);
    $driver = $this->driver;
    $sql = $driver->loadSqlClass($this->model)->buildInsertSql($driver);
    $this->_execute($stmt->setSql($sql));

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
    $this->model->setSaveValues($this->chooseValues($data, "update"));
    $stmt = Sabel_DB_Statement::create(Sabel_DB_Statement::UPDATE);
    $driver = $this->driver;
    $sql  = $driver->loadSqlClass($this->model)->buildUpdateSql($driver);
    $sql .= $this->createConditionSql();
    $this->_execute($stmt->setSql($sql));
  }

  protected function chooseValues($data, $method)
  {
    if (isset($data) && !is_array($data)) {
      throw new Sabel_DB_Exception("{$method}() argument should be an array.");
    } else {
      return ($data === null) ? $this->model->toArray() : $data;
    }
  }

  public function arrayInsert(array $data)
  {
    $this->method    = "arrayInsert";
    $this->arguments = array($data);

    return $this;
  }

  public function _arrayInsert()
  {
    list ($data) = $this->arguments;

    Sabel_DB_Transaction::begin($this->model->getConnectionName());

    $driver = $this->driver;
    $this->model->setSaveValues($data);
    $sqls = $driver->loadSqlClass($this->model)->buildInsertSql($driver);
    $stmt = Sabel_DB_Statement::create(Sabel_DB_Statement::INSERT);
    $this->_execute($stmt->setSql($sqls));

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

    $stmt = Sabel_DB_Statement::createDeleteStatement($this);
    $this->_execute($stmt);
  }

  public function query($sql, $assoc = false, $stmtType = Sabel_DB_Statement::SELECT)
  {
    $this->method = "query";
    $this->arguments = array($sql, $assoc, $stmtType);

    return $this;
  }

  protected function _query()
  {
    list ($sql, $assoc, $stmtType) = $this->arguments;

    $stmt = Sabel_DB_Statement::create($stmtType);
    $rows = $this->_execute($stmt->setSql($sql));

    if (empty($rows)) return null;

    if ($assoc) {
      return $rows;
    } else {
      $results = array();
      foreach ($rows as $row) $results[] = (object)$row;
      return $results;
    }
  }

  public function setIncrementId($id)
  {
    $this->incrementId = $id;
  }

  protected function driverInterrupt($type, $stmtType)
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
      return $driver->$method($this);
    }
  }

  protected function createSelectSql()
  {
    $driver     = $this->driver;
    $projection = $this->projection;
    $sqlBulder  = $driver->loadSqlClass($this->model);
    return $sqlBulder->buildSelectSql($driver, $projection);
  }

  protected function createConditionSql($manager = null)
  {
    if ($manager === null) {
      $manager = $this->conditionManager;
    }

    if ($manager !== null && !$manager->isEmpty()) {
      return $manager->build($this->driver);
    } else {
      return "";
    }
  }

  protected function createConstraintSql($sql, $constraints = null)
  {
    if ($constraints === null) {
      $constraints = $this->constraints;
    }

    if (!empty($constraints)) {
      $builder = $this->driver->loadConstraintSqlClass();
      return $builder->build($sql, $constraints);
    } else {
      return $sql;
    }
  }
}

function _sc_cb_func($matches)
{
  return convert_to_tablename(trim($matches[0]));
}
