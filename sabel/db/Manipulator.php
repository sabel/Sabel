<?php

/**
 * Sabel_DB_Manipulator
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Manipulator
{
  protected
    $model  = null,
    $stmt   = null,
    $method = "";

  protected
    $projection       = "*",
    $arguments        = array(),
    $constraints      = array(),
    $conditionManager = null,
    $autoReinit       = true;

  public function __construct($model = null)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }

    $this->setModel($model, false);
  }

  public function setModel(Sabel_DB_Abstract_Model $model, $reinit = true)
  {
    $this->model = $model;
    if ($reinit) $this->initState();

    return $this;
  }

  public function getModel()
  {
    return $this->model;
  }

  public function before($method)
  {
    return;
  }

  public function after($method, $result)
  {
    return;
  }

  protected function prepare($method, $args)
  {
    $this->arguments = $args;
    $this->method = $method;

    return $this;
  }

  public final function execute()
  {
    $method = $this->method;
    $result = $this->before($method);

    if ($result === null) {
      $execMethod = "_" . $method;
      $result = $this->$execMethod();
    }

    $afterResult = $this->after($method, $result);
    if ($afterResult !== null) $result = $afterResult;

    if ($this->autoReinit) $this->initState();

    return $result;
  }

  protected final function _execute(Sabel_DB_Abstract_Statement $stmt)
  {
    $this->stmt = $stmt;

    try {
      if (Sabel_DB_Transaction::isActive()) {
        Sabel_DB_Transaction::begin($this->model->getConnectionName());
      }
      return $stmt->execute();
    } catch (Exception $e) {
      Sabel_DB_Transaction::rollback();
      throw new Sabel_DB_Exception($e->getMessage());
    }
  }

  public function autoReinit($bool)
  {
    $this->autoReinit = $bool;
  }

  public function initState()
  {
    $this->unsetConditions(true);

    $this->method     = "";
    $this->projection = "*";
    $this->arguments  = array();
    $this->parents    = array();
  }

  public function setProjection($p)
  {
    $this->projection = (is_array($p)) ? join(", ", $p) : $p;
  }

  public function loadConditionManager()
  {
    if ($this->conditionManager === null) {
      return $this->conditionManager = new Sabel_DB_Condition_Manager();
    } else {
      return $this->conditionManager;
    }
  }

  public function setCondition($arg1, $arg2 = null)
  {
    if (empty($arg1)) return;

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
    $args = func_get_args();
    $this->prepare("getCount", $args);

    return $this->execute();
  }

  protected function _getCount()
  {
    @list ($arg1, $arg2) = $this->arguments;
    $this->setCondition($arg1, $arg2);

    $projection  = $this->projection;
    $constraints = $this->constraints;
    $this->projection  = "COUNT(*) AS cnt";
    $this->constraints = array("limit" => 1);

    $stmt = $this->createStatement(Sabel_DB_Statement::SELECT);
    $rows = $this->_execute($this->prepareSelect($stmt));

    $this->projection  = $projection;
    $this->constraints = $constraints;

    return (int)$rows[0]["cnt"];
  }

  public function selectOne($arg1 = null, $arg2 = null)
  {
    $args = func_get_args();
    $this->prepare("selectOne", $args);

    return $this->execute();
  }

  protected function _selectOne()
  {
    @list ($arg1, $arg2) = $this->arguments;

    if ($arg1 === null && $this->conditionManager === null) {
      throw new Sabel_DB_Exception("selectOne() must set the condition.");
    }

    $this->setCondition($arg1, $arg2);
    return $this->createModel($this->model);
  }

  protected function createModel($model)
  {
    $stmt = $this->createStatement(Sabel_DB_Statement::SELECT);
    $rows = $this->_execute($this->prepareSelect($stmt));

    if (isset($rows[0])) {
      $model->setAttributes($rows[0]);
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
    $args = func_get_args();
    $this->prepare("select", $args);

    return $this->execute();
  }

  protected function _select()
  {
    @list ($arg1, $arg2) = $this->arguments;

    $this->setCondition($arg1, $arg2);
    $stmt = $this->createStatement(Sabel_DB_Statement::SELECT);
    $rows = $this->_execute($this->prepareSelect($stmt));

    if (empty($rows)) return false;

    $results = array();
    $source  = MODEL($this->model->getName());

    foreach ($rows as $row) {
      $model = clone $source;
      $model->setAttributes($row);
      $results[] = $model;
    }

    return $results;
  }

  public function getChild($childName, $constraints = null)
  {
    $args = func_get_args();
    $this->prepare("getChild", $args);

    return $this->execute();
  }

  protected function _getChild()
  {
    @list ($childName, $constraints) = $this->arguments;

    $model   = $this->model;
    $child   = MODEL($childName);
    $foreign = $child->getSchema()->getForeignKeys();
    $tblName = $model->getTableName();

    if ($foreign === null) {
      $col  = "id";
      $fkey = $tblName . "_id";
    } else {
      foreach ($foreign as $fkey => $params) {
        if ($params["referenced_table"] === $tblName) {
          $col = $params["referenced_column"];
          break;
        }
      }
    }

    $self  = get_class($this);
    $manip = new $self($child);

    if (!empty($constraints)) {
      $manip->setConstraint($constraints);
    }

    return $manip->select($fkey, $model->__get($col));
  }

  public function validate($ignores = null)
  {
    $args = func_get_args();
    $this->prepare("validate", $args);

    return $this->execute();
  }

  public function _validate()
  {
    @list ($ignores) = $this->arguments;

    if ($ignores === null) {
      $ignores = array();
    } elseif (is_string($ignores)) {
      $ignores = array($ignores);
    }

    $validator = new Sabel_DB_Validator($this->model);
    return $validator->validate($ignores);
  }

  public function save()
  {
    $args = func_get_args();
    $this->prepare("save", $args);

    return $this->execute();
  }

  protected function _save()
  {
    if ($this->model->isSelected()) {
      $saveValues = $this->_saveUpdate();
    } else {
      $saveValues = $this->_saveInsert();
    }

    $model = MODEL($this->model->getName());
    $model->setAttributes($saveValues);

    return $model;
  }

  protected function _saveInsert()
  {
    $model = $this->model;
    $saveValues = $model->toArray();

    foreach ($saveValues as $key => $val) {
      $saveValues[$key] = $model->__get($key);
    }

    $stmt  = $this->createStatement(Sabel_DB_Statement::INSERT);
    $newId = $this->_execute($this->prepareInsert($stmt, $saveValues));

    if ($newId !== null) {
      $column = $model->getSequenceColumn();
      $saveValues[$column] = $newId;
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

    $stmt = $this->createStatement(Sabel_DB_Statement::UPDATE);
    $this->_execute($this->prepareUpdate($stmt, $saveValues));

    return array_merge($model->toArray(), $saveValues);
  }

  public function insert($data = null)
  {
    $args = func_get_args();
    $this->prepare("insert", $args);

    return $this->execute();
  }

  protected function _insert()
  {
    @list ($data) = $this->arguments;

    $stmt = $this->createStatement(Sabel_DB_Statement::INSERT);
    return $this->_execute($this->prepareInsert($stmt, $data));
  }

  public function update($data = null)
  {
    $args = func_get_args();
    $this->prepare("update", $args);

    return $this->execute();
  }

  protected function _update($data = null)
  {
    @list ($data) = $this->arguments;
    $stmt = $this->createStatement(Sabel_DB_Statement::UPDATE);
    $this->_execute($this->prepareUpdate($stmt, $data));
  }

  public function delete($arg1 = null, $arg2 = null)
  {
    $args = func_get_args();
    $this->prepare("delete", $args);

    return $this->execute();
  }

  protected function _delete()
  {
    $model   = $this->model;
    $manager = $this->loadConditionManager();

    @list ($arg1, $arg2) = $this->arguments;

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

    $stmt = $this->createStatement(Sabel_DB_Statement::DELETE);
    $this->_execute($this->prepareDelete($stmt));
  }

  public function executeStatement($stmt)
  {
    $this->arguments = array($stmt);
    $this->method    = "executeStatement";

    return $this->execute();
  }

  protected function _executeStatement()
  {
    return $this->_execute($this->arguments[0]);
  }

  public function query($sql, $assoc = false, $stmtType = Sabel_DB_Statement::SELECT)
  {
    $this->method = "query";
    $this->arguments = array($sql, $assoc, $stmtType);

    return $this->execute();
  }

  protected function _query()
  {
    list ($sql, $assoc, $stmtType) = $this->arguments;

    $stmt = $this->createStatement($stmtType);
    $rows = $this->_execute($stmt->setSql($sql));

    if (empty($rows) || $assoc === null) {
      return null;
    } elseif ($assoc) {
      return $rows;
    } else {
      $results = array();
      foreach ($rows as $row) $results[] = (object)$row;
      return $results;
    }
  }

  protected function createStatement($stmtType)
  {
    return Sabel_DB_Statement::create($this->model, $stmtType);
  }

  protected function prepareSelect($stmt)
  {
    $model = $this->model;

    if (($projection = $this->projection) === "*") {
      $projection = implode(", ", $this->model->getColumnNames());
    }

    $stmt->table($model->getTableName());
    $stmt->projection($projection);
    $stmt->where($this->loadConditionManager()->build($stmt));
    $stmt->constraints($this->constraints);

    return $stmt;
  }

  protected function prepareUpdate($stmt, $data)
  {
    $values = $this->chooseValues($data, "update");

    $stmt->setBindValues($values, false);
    $stmt->table($this->model->getTableName());
    $stmt->values($values);
    $stmt->where($this->loadConditionManager()->build($stmt));

    return $stmt;
  }

  protected function prepareInsert($stmt, $data)
  {
    $values = $this->chooseValues($data, "insert");

    $stmt->setBindValues($values, false);
    $stmt->table($this->model->getTableName());
    $stmt->values($values);
    $stmt->sequenceColumn($this->model->getSequenceColumn());

    return $stmt;
  }

  protected function prepareDelete($stmt)
  {
    $stmt->table($this->model->getTableName());
    $stmt->where($this->loadConditionManager()->build($stmt));

    return $stmt;
  }

  protected function chooseValues($data, $method)
  {
    if (isset($data) && !is_array($data)) {
      throw new Sabel_DB_Exception("{$method}() argument should be an array.");
    } else {
      $data = ($data === null) ? $this->model->toArray() : $data;

      if (empty($data)) {
        throw new Sabel_DB_Exception("empty $method values.");
      } else {
        return $data;
      }
    }
  }
}

function _sc_cb_func($matches)
{
  return convert_to_tablename(trim($matches[0]));
}
