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
class Sabel_DB_Manipulator extends Sabel_Object
{
  protected
    $model  = null,
    $sql    = null,
    $method = "";

  protected
    $projection       = array(),
    $arguments        = array(),
    $constraints      = array(),
    $conditionManager = null,
    $autoReinit       = true;

  public function __construct($model)
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

  public function set($key, $val)
  {
    $this->model->__set($key, $val);
  }

  public function get($key)
  {
    return $this->model->__get($key);
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

  protected final function execute()
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

  protected final function _execute(Sabel_DB_Abstract_Statement $sql)
  {
    $this->sql = $sql;
    return $sql->execute();
  }

  public function autoReinit($bool)
  {
    $this->autoReinit = $bool;
  }

  public function initState()
  {
    $this->unsetConditions(true);

    $this->method     = "";
    $this->projection = array();
    $this->arguments  = array();
  }

  public function setProjection(array $projection)
  {
    $this->projection = $projection;
  }

  public function getProjection()
  {
    return $this->projection;
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
    } elseif (is_object($arg1)) {
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
    $this->projection  = array("COUNT(*) AS cnt");
    $this->constraints = array("limit" => 1);

    $sql  = $this->getStatement(Sabel_DB_Statement::SELECT);
    $rows = $this->_execute($this->prepareSelect($sql));

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

    $model = clone $this->model;
    $sql   = $this->getStatement(Sabel_DB_Statement::SELECT);
    $rows  = $this->_execute($this->prepareSelect($sql));

    if (isset($rows[0])) {
      $model->setAttributes($rows[0]);
    } else {
      $manager = $this->loadConditionManager();
      $conditions = $manager->getConditions();

      foreach ($conditions as $condition) {
        if ($manager->isIndividualCondition($condition)) {
          $model->__set($condition->column(), $condition->value());
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
    $sql  = $this->getStatement(Sabel_DB_Statement::SELECT);
    $rows = $this->_execute($this->prepareSelect($sql));

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

  public function save()
  {
    $args = func_get_args();
    $this->prepare("save", $args);

    return $this->execute();
  }

  protected function _save()
  {
    $new = MODEL($this->model->getName());

    if ($this->model->isSelected()) {
      return $new->setAttributes($this->_saveUpdate());
    } else {
      return $new->setAttributes($this->_saveInsert());
    }
  }

  protected function _saveInsert()
  {
    $model = $this->model;
    $columns = $model->getColumns();
    $saveValues = $model->toArray();

    $sql   = $this->getStatement(Sabel_DB_Statement::INSERT);
    $newId = $this->_execute($this->prepareInsert($sql, $saveValues));

    if ($newId !== null && ($column = $model->getSequenceColumn()) !== null) {
      $saveValues[$column] = $newId;
    }

    foreach ($columns as $name => $column) {
      if (!array_key_exists($name, $saveValues)) {
        $saveValues[$name] = $column->default;
      }
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

    $sql = $this->getStatement(Sabel_DB_Statement::UPDATE);
    $saveValues = $model->getUpdateValues();
    $this->_execute($this->prepareUpdate($sql, $saveValues));

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

    $sql = $this->getStatement(Sabel_DB_Statement::INSERT);
    return $this->_execute($this->prepareInsert($sql, $data));
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
    $sql = $this->getStatement(Sabel_DB_Statement::UPDATE);
    $this->_execute($this->prepareUpdate($sql, $data));
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

    $sql = $this->getStatement(Sabel_DB_Statement::DELETE);
    $this->_execute($this->prepareDelete($sql));
  }

  public function executeSql($sql)
  {
    $this->arguments = array($sql);
    $this->method    = "executeSql";

    return $this->execute();
  }

  protected function _executeSql()
  {
    return $this->_execute($this->arguments[0]);
  }

  public function getStatement($type)
  {
    $model = $this->model;
    $sql = Sabel_DB_Driver::createStatement($model->getConnectionName());
    return $sql->table($model->getTableName())->setType($type);
  }

  protected function prepareSelect($sql)
  {
    return $sql->projection($this->projection)
               ->where($this->loadConditionManager()->build($sql))
               ->constraints($this->constraints);
  }

  protected function prepareUpdate($sql, $data)
  {
    $tblName = $this->model->getTableName();
    $values  = $this->chooseValues($data, "update");

    return $sql->values($values)->where($this->loadConditionManager()->build($sql));
  }

  protected function prepareInsert($sql, $data)
  {
    $tblName = $this->model->getTableName();
    $values  = $this->chooseValues($data, "insert");

    return $sql->values($values)->sequenceColumn($this->model->getSequenceColumn());
  }

  protected function prepareDelete($sql)
  {
    return $sql->where($this->loadConditionManager()->build($sql));
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
