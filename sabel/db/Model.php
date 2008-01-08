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
abstract class Sabel_DB_Model extends Sabel_Object
{
  protected
    $connectionName = "default";
    
  protected
    $method    = "",
    $arguments = array();
    
  protected
    $tableName  = "",
    $modelName  = "",
    $schema     = null,
    $schemaCols = array(),
    $projection = array(),
    $selected   = false;
    
  protected
    $constraints = array(),
    $condition   = null,
    $autoReinit  = true;
    
  protected
    $values       = array(),
    $updateValues = array();
    
  public function __construct($id = null)
  {
    $this->initialize();
    if ($id !== null) $this->initSelectOne($id);
  }

  protected function initialize($mdlName = null)
  {
    if ($mdlName === null) {
      $mdlName = get_class($this);
    }
    
    $this->modelName = $mdlName;
    
    if ($this->tableName === "") {
      $this->tableName = convert_to_tablename($mdlName);
    }
    
    $this->schema = Sabel_DB_Schema::getTableInfo($this->tableName, $this->connectionName);
    $this->schemaCols = $this->schema->getColumns();
  }
  
  public function setConnectionName($connectionName)
  {
    $this->connectionName = $connectionName;
  }
  
  public function getConnectionName()
  {
    return $this->connectionName;
  }
  
  public function __set($key, $val)
  {
    if (isset($this->schemaCols[$key])) {
      $val = $this->schemaCols[$key]->cast($val);
    }
    
    $this->values[$key] = $val;
    if ($this->selected) $this->updateValues[$key] = $val;
  }
  
  public function unsetValue($key)
  {
    unset($this->values[$key]);
    unset($this->updateValues[$key]);
  }
  
  public function setValues(array $values)
  {
    foreach ($values as $key => $val) {
      $this->__set($key, $val);
    }
  }
  
  public function __get($key)
  {
    if (isset($this->values[$key])) {
      return $this->values[$key];
    } else {
      return null;
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
  
  public function getName()
  {
    return $this->modelName;
  }
  
  public function getColumnNames()
  {
    return array_keys($this->schemaCols);
  }
  
  public function getSchema()
  {
    return $this->schema;
  }
  
  public function getColumns()
  {
    return $this->schemaCols;
  }
  
  public function toArray()
  {
    return $this->values;
  }
  
  public function isSelected()
  {
    return $this->selected;
  }
  
  public function setProperties($attributes)
  {
    $pkey = $this->schema->getPrimaryKey();
    if (is_string($pkey)) $pkey = (array)$pkey;
    
    if (empty($pkey)) {
      $selected = false;
    } else {
      $selected = true;
      foreach ($pkey as $key) {
        if (!isset($attributes[$key])) {
          $selected = false;
          break;
        }
      }
    }
    
    $columns = $this->schemaCols;
    foreach ($attributes as $key => &$val) {
      if (isset($columns[$key])) {
        $val = $columns[$key]->cast($val);
      }
    }
    
    $this->values   = $attributes;
    $this->selected = $selected;
    
    return $this;
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
  
  public function autoReinit($bool)
  {
    $this->autoReinit = $bool;
  }
  
  public function initState()
  {
    $this->clearCondition(true);
    
    $this->method     = "";
    $this->projection = array();
    $this->arguments  = array();
  }
  
  public function setProjection($projection)
  {
    $this->projection = $projection;
  }
  
  public function getProjection()
  {
    return $this->projection;
  }
  
  public function getCondition()
  {
    if ($this->condition === null) {
      $this->condition = new Sabel_DB_Condition_Manager();
    }
    
    return $this->condition;
  }
  
  public function setCondition($arg1, $arg2 = null)
  {
    if (empty($arg1)) return;
    
    $condition = $this->getCondition();
    
    if (is_array($arg1)) {
      $condition->create($arg1);
    } elseif ($arg1 instanceof Sabel_DB_Abstract_Condition) {
      $condition->add($arg1);
    } elseif (is_model($arg1)) {
      $joinkey = create_join_key($this, $arg1->getTableName());
      $colName = $this->getName() . "." . $joinkey["fkey"];
      $condition->create($colName, $arg1->$joinkey["id"]);
    } elseif ($arg2 === null) {
      $colName = $this->getName() . "." . $this->schema->getPrimaryKey();
      $condition->create($colName, $arg1);
    } else {
      $condition->create($arg1, $arg2);
    }
  }
  
  public function setOrderBy($orderBy)
  {
    if (is_string($orderBy)) {
      $this->constraints["order"] = $orderBy;
      return $this;
    } else {
      $message = "argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function setLimit($limit)
  {
    if (is_numeric($limit)) {
      $this->constraints["limit"] = $limit;
      return $this;
    } else {
      $message = "argument must be an integer.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function setOffset($offset)
  {
    if (is_numeric($offset)) {
      $this->constraints["offset"] = $offset;
      return $this;
    } else {
      $message = "argument must be an integer.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function getConstraints()
  {
    return $this->constraints;
  }
  
  public function clearCondition($andConstraints = false)
  {
    if ($this->condition !== null) {
      $this->condition->clear();
    }
    
    if ($andConstraints) $this->unsetConstraints();
  }
  
  public function unsetConstraints()
  {
    $this->constraints = array();
  }
  
  public function getCount($arg1 = null, $arg2 = null)
  {
    $args = func_get_args();
    return $this->prepare("getCount", $args)->execute();
  }
  
  protected function _getCount()
  {
    @list ($arg1, $arg2) = $this->arguments;
    $this->setCondition($arg1, $arg2);
    
    $projection  = $this->projection;
    $constraints = $this->constraints;
    $this->projection  = "COUNT(*) AS cnt";
    $this->constraints = array("limit" => 1);
    
    $stmt = $this->getStatement(Sabel_DB_Statement::SELECT);
    $rows = $this->prepareSelect($stmt)->execute();
    
    $this->projection  = $projection;
    $this->constraints = $constraints;
    
    return (int)$rows[0]["cnt"];
  }
  
  public function selectOne($arg1 = null, $arg2 = null)
  {
    $args = func_get_args();
    return $this->prepare("selectOne", $args)->execute();
  }
  
  protected function _selectOne()
  {
    @list ($arg1, $arg2) = $this->arguments;
    
    if ($arg1 === null && $this->condition === null) {
      throw new Sabel_DB_Exception("selectOne() must set the condition.");
    }
    
    $this->setCondition($arg1, $arg2);
    $model = MODEL($this->modelName);
    $this->_doSelectOne($model);
    
    return $model;
  }
  
  protected function initSelectOne($id)
  {
    $this->setCondition($id);
    $this->_doSelectOne($this);
  }
  
  protected function _doSelectOne(Sabel_DB_Model $model)
  {
    $stmt = $this->getStatement(Sabel_DB_Statement::SELECT);
    $rows = $this->prepareSelect($stmt)->execute();
    
    if (isset($rows[0])) {
      $model->setProperties($rows[0]);
    } else {
      $condition  = $this->getCondition();
      $conditions = $condition->getConditions();
      
      foreach ($conditions as $c) {
        if ($condition->isIndividualCondition($c)) {
          $model->__set($c->column(), $c->value());
        }
      }
    }
  }
  
  public function select($arg1 = null, $arg2 = null)
  {
    $args = func_get_args();
    return $this->prepare("select", $args)->execute();
  }
  
  protected function _select()
  {
    @list ($arg1, $arg2) = $this->arguments;
    
    $this->setCondition($arg1, $arg2);
    $stmt = $this->getStatement(Sabel_DB_Statement::SELECT);
    $rows = $this->prepareSelect($stmt)->execute();
    return (empty($rows)) ? array() : $this->toModels($rows);
  }
  
  public function selectByQuery($query, $bindValues = array())
  {
    if (is_string($query)) {
      $args = func_get_args();
      return $this->prepare("selectByQuery", $args)->execute();
    } else {
      $message = "argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  protected function _selectByQuery()
  {
    $stmt = $this->getStatement(Sabel_DB_Statement::SELECT);
    $stmt->projection($this->projection)->where(" " . $this->arguments[0]);
    
    if (isset($this->arguments[1])) {
      $stmt->setBindValues($this->arguments[1]);
    }
    
    $rows = $stmt->execute();
    return (empty($rows)) ? array() : $this->toModels($rows);
  }
  
  protected function toModels(array $rows)
  {
    $results = array();
    
    $source = MODEL($this->modelName);
    foreach ($rows as $row) {
      $model = clone $source;
      $model->setProperties($row);
      $results[] = $model;
    }
    
    return $results;
  }
  
  public function save()
  {
    $args = func_get_args();
    return $this->prepare("save", $args)->execute();
  }
  
  protected function _save()
  {
    $new = MODEL($this->modelName);
    
    if ($this->isSelected()) {
      return $new->setProperties($this->_saveUpdate());
    } else {
      return $new->setProperties($this->_saveInsert());
    }
  }
  
  protected function _saveInsert()
  {
    $columns = $this->getColumns();
    $saveValues = $this->values;
    
    $stmt  = $this->getStatement(Sabel_DB_Statement::INSERT);
    $newId = $this->prepareInsert($stmt, $saveValues)->execute();
    
    if ($newId !== null && ($column = $this->schema->getSequenceColumn()) !== null) {
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
    if (($pkey = $this->schema->getPrimaryKey()) === null) {
      $message = "save() cannot update model(there is not primary key).";
      throw new Sabel_DB_Exception($message);
    } else {
      if (is_string($pkey)) $pkey = array($pkey);
      
      foreach ($pkey as $key) {
        $this->setCondition($key, $this->__get($key));
      }
    }
    
    $stmt = $this->getStatement(Sabel_DB_Statement::UPDATE);
    $saveValues = $this->updateValues;
    $this->prepareUpdate($stmt, $saveValues)->execute();
    
    return array_merge($this->values, $saveValues);
  }
  
  public function insert($data = null)
  {
    $args = func_get_args();
    return $this->prepare("insert", $args)->execute();
  }
  
  protected function _insert()
  {
    @list ($data) = $this->arguments;
    
    $stmt = $this->getStatement(Sabel_DB_Statement::INSERT);
    return $this->prepareInsert($stmt, $data)->execute();
  }
  
  public function update($data = null)
  {
    $args = func_get_args();
    return $this->prepare("update", $args)->execute();
  }
  
  protected function _update($data = null)
  {
    @list ($data) = $this->arguments;
    $stmt = $this->getStatement(Sabel_DB_Statement::UPDATE);
    $this->prepareUpdate($stmt, $data)->execute();
  }
  
  public function delete($arg1 = null, $arg2 = null)
  {
    $args = func_get_args();
    return $this->prepare("delete", $args)->execute();
  }
  
  protected function _delete()
  {
    $condition = $this->getCondition();
    @list ($arg1, $arg2) = $this->arguments;
    
    if (!$this->isSelected() && $arg1 === null && $condition->isEmpty()) {
      $message = "delete() must set the condition.";
      throw new Sabel_DB_Exception($message);
    }
    
    if ($arg1 !== null) {
      $this->setCondition($arg1, $arg2);
    } elseif ($this->isSelected()) {
      if (($pkey = $this->schema->getPrimaryKey()) === null) {
        $message = "delete() cannot delete model(there is not primary key).";
        throw new Sabel_DB_Exception($message);
      } else {
        if (is_string($pkey)) $pkey = (array)$pkey;
        
        foreach ($pkey as $key) {
          $this->setCondition($key, $this->__get($key));
        }
      }
    }
    
    $stmt = $this->getStatement(Sabel_DB_Statement::DELETE);
    $this->prepareDelete($stmt)->execute();
  }
  
  public function getStatement($type)
  {
    $stmt = Sabel_DB_Driver::createStatement($this->connectionName);
    return $stmt->table($this->tableName)->type($type);
  }
  
  protected function prepareSelect($stmt)
  {
    return $stmt->projection($this->projection)
                ->where($this->getCondition()->build($stmt))
                ->constraints($this->constraints);
  }
  
  protected function prepareUpdate($stmt, $data)
  {
    $values = $this->chooseValues($data, "update");
    return $stmt->values($values)->where($this->getCondition()->build($stmt));
  }
  
  protected function prepareInsert($stmt, $data)
  {
    $values = $this->chooseValues($data, "insert");
    return $stmt->values($values)->sequenceColumn($this->schema->getSequenceColumn());
  }
  
  protected function prepareDelete($stmt)
  {
    return $stmt->where($this->getCondition()->build($stmt));
  }
  
  protected function chooseValues($data, $method)
  {
    if (isset($data) && !is_array($data)) {
      throw new Sabel_Exception_InvalidArgument("argument should be an array.");
    } else {
      $data = ($data === null) ? $this->values : $data;
      
      if (empty($data)) {
        throw new Sabel_DB_Exception("empty $method values.");
      } else {
        return $data;
      }
    }
  }
}
