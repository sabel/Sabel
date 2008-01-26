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
  /**
   * @var string
   */
  protected $connectionName = "default";
  
  /**
   * @var string
   */
  protected $method = "";
  
  /**
   * @var array
   */
  protected $arguments = array();
  
  /**
   * @var string
   */
  protected $tableName = "";
  
  /**
   * @var string
   */
  protected $modelName = "";
  
  /**
   * @var Sabel_DB_Schema_Table
   */
  protected $schema = null;
  
  /**
   * @var array
   */
  protected $schemaCols = array();
  
  /**
   * @var array
   */
  protected $projection = array();
  
  /**
   * @var boolean
   */
  protected $selected = false;
  
  /**
   * @var array
   */
  protected $constraints = array();
  
  /**
   * @var Sabel_DB_Condition_Manager
   */
  protected $condition = null;
  
  /**
   * @var boolean
   */
  protected $autoReinit = true;
  
  /**
   * @var array
   */
  protected $values = array();
  
  /**
   * @var array
   */
  protected $updateValues = array();
  
  public function __construct($id = null)
  {
    $this->initialize();
    if ($id !== null) $this->initSelectOne($id);
  }

  /**
   * @param string $mdlName
   *
   * @return void
   */
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
  
  /**
   * @param string $connectionName
   *
   * @return void
   */
  public function setConnectionName($connectionName)
  {
    $this->connectionName = $connectionName;
  }
  
  /**
   * @return string
   */
  public function getConnectionName()
  {
    return $this->connectionName;
  }
  
  /**
   * @param string $key
   * @param mixed  $val
   *
   * @return void
   */
  public function __set($key, $val)
  {
    $this->values[$key] = $val;
    if ($this->selected) $this->updateValues[$key] = $val;
  }
  
  /**
   * @param string $key
   *
   * @return void
   */
  public function unsetValue($key)
  {
    unset($this->values[$key]);
    unset($this->updateValues[$key]);
  }
  
  /**
   * @param array $values
   *
   * @return Sabel_DB_Model
   */
  public function setValues(array $values)
  {
    foreach ($values as $key => $val) {
      $this->__set($key, $val);
    }
    
    return $this;
  }
  
  /**
   * @param string $key
   *
   * @return mixed
   */
  public function __get($key)
  {
    if (isset($this->values[$key])) {
      $value = $this->values[$key];
      if (isset($this->schemaCols[$key])) {
        return $this->schemaCols[$key]->cast($value);
      } else {
        return $value;
      }
    } else {
      return null;
    }
  }
  
  /**
   * @return array
   */
  public function getUpdateValues()
  {
    return $this->updateValues;
  }
  
  /**
   * @param string $tblName
   *
   * @return void
   */
  public function setTableName($tblName)
  {
    $this->tableName = $tblName;
  }
  
  /**
   * @return string
   */
  public function getTableName()
  {
    return $this->tableName;
  }
  
  /**
   * @return string
   */
  public function getName()
  {
    return $this->modelName;
  }
  
  /**
   * @return array
   */
  public function getColumnNames()
  {
    return array_keys($this->schemaCols);
  }
  
  /**
   * @return Sabel_DB_Schema_Table
   */
  public function getSchema()
  {
    return $this->schema;
  }
  
  /**
   * @return Sabel_DB_Schema_Column[]
   */
  public function getColumns()
  {
    return $this->schemaCols;
  }
  
  /**
   * @return array
   */
  public function toArray()
  {
    return $this->values;
  }
  
  /**
   * @return boolean
   */
  public function isSelected()
  {
    return $this->selected;
  }
  
  /**
   * @param array $properties
   *
   * @return Sabel_DB_Model
   */
  public function setProperties(array $properties)
  {
    $pkey = $this->schema->getPrimaryKey();
    if (is_string($pkey)) $pkey = (array)$pkey;
    
    if (empty($pkey)) {
      $selected = false;
    } else {
      $selected = true;
      foreach ($pkey as $key) {
        if (!isset($properties[$key])) {
          $selected = false;
          break;
        }
      }
    }
    
    $this->values   = $properties;
    $this->selected = $selected;
    
    return $this;
  }
  
  /**
   * @param string $method
   * @param array  $args
   *
   * @return Sabel_DB_Model
   */
  protected function prepare($method, $args)
  {
    $this->arguments = $args;
    $this->method = $method;
    
    return $this;
  }
  
  /**
   * @return mixed
   */
  protected final function execute()
  {
    $result = null;
    $method = $this->method;
    
    $beforeMethod = "before" . ucfirst($method);
    $afterMethod  = "after"  . ucfirst($method);
    
    if ($this->hasMethod($beforeMethod)) {
      $result = $this->$beforeMethod();
    }
    
    if ($result === null) {
      $execMethod = "_" . $method;
      $result = $this->$execMethod();
    }
    
    if ($this->hasMethod($afterMethod)) {
      $afterResult = $this->$afterMethod();
      if ($afterResult !== null) $result = $afterResult;
    }
    
    if ($this->autoReinit) $this->initState();
    
    return $result;
  }
  
  /**
   * @param boolean $bool
   *
   * @return void
   */
  public function autoReinit($bool)
  {
    $this->autoReinit = $bool;
  }
  
  /**
   * @return void
   */
  public function initState()
  {
    $this->clearCondition(true);
    
    $this->method     = "";
    $this->projection = array();
    $this->arguments  = array();
  }
  
  /**
   * @param mixed $projection
   *
   * @return Sabel_DB_Model
   */
  public function setProjection($projection)
  {
    $this->projection = $projection;
    
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function getProjection()
  {
    return $this->projection;
  }
  
  /**
   * @return Sabel_DB_Condition_Manager
   */
  public function getCondition()
  {
    if ($this->condition === null) {
      $this->condition = new Sabel_DB_Condition_Manager();
    }
    
    return $this->condition;
  }
  
  /**
   * @param mixed $arg1
   * @param mixed $arg2
   *
   * @return void
   */
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
  
  /**
   * @param string $orderBy
   *
   * @return Sabel_DB_Model
   */
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
  
  /**
   * @param int $limit
   *
   * @return Sabel_DB_Model
   */
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
  
  /**
   * @param int $offset
   *
   * @return Sabel_DB_Model
   */
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
  
  /**
   * @return array
   */
  public function getConstraints()
  {
    return $this->constraints;
  }
  
  /**
   * @param boolean $andConstraints
   *
   * @return void
   */
  public function clearCondition($andConstraints = false)
  {
    if ($this->condition !== null) {
      $this->condition->clear();
    }
    
    if ($andConstraints) $this->unsetConstraints();
  }
  
  /**
   * @return void
   */
  public function unsetConstraints()
  {
    $this->constraints = array();
  }
  
  /**
   * @param mixed $arg1
   * @param mixed $arg2
   *
   * @return int
   */
  public function getCount($arg1 = null, $arg2 = null)
  {
    $args = func_get_args();
    return $this->prepare("getCount", $args)->execute();
  }
  
  /**
   * @return int
   */
  protected function _getCount()
  {
    @list ($arg1, $arg2) = $this->arguments;
    $this->setCondition($arg1, $arg2);
    
    $projection = $this->projection;
    $this->projection  = "COUNT(*) AS cnt";
    
    $stmt = $this->getStatement(Sabel_DB_Statement::SELECT);
    $rows = $this->prepareSelect($stmt)->execute();
    
    $this->projection  = $projection;
    
    return (int)$rows[0]["cnt"];
  }
  
  /**
   * @param mixed $arg1
   * @param mixed $arg2
   *
   * @return Sabel_DB_Model
   */
  public function selectOne($arg1 = null, $arg2 = null)
  {
    $args = func_get_args();
    return $this->prepare("selectOne", $args)->execute();
  }
  
  /**
   * @return Sabel_DB_Model
   */
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
  
  /**
   * @param mixed $id
   *
   * @return void
   */
  protected function initSelectOne($id)
  {
    $this->setCondition($id);
    $this->_doSelectOne($this);
  }
  
  /**
   * @param Sabel_DB_Model $model
   *
   * @return void
   */
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
  
  /**
   * @param mixed $arg1
   * @param mixed $arg2
   *
   * @return array
   */
  public function select($arg1 = null, $arg2 = null)
  {
    $args = func_get_args();
    return $this->prepare("select", $args)->execute();
  }
  
  /**
   * @return array
   */
  protected function _select()
  {
    @list ($arg1, $arg2) = $this->arguments;
    
    $this->setCondition($arg1, $arg2);
    $stmt = $this->getStatement(Sabel_DB_Statement::SELECT);
    $rows = $this->prepareSelect($stmt)->execute();
    
    return (empty($rows)) ? array() : $this->toModels($rows);
  }
  
  /**
   * @param string $query
   * @param array  $bindValues
   *
   * @return array
   */
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
  
  /**
   * @return array
   */
  protected function _selectByQuery()
  {
    $stmt = $this->getStatement(Sabel_DB_Statement::SELECT);
    $stmt->projection($this->projection)->where($this->arguments[0]);
    
    if (isset($this->arguments[1])) {
      $stmt->setBindValues($this->arguments[1]);
    }
    
    $rows = $stmt->execute();
    return (empty($rows)) ? array() : $this->toModels($rows);
  }
  
  /**
   * @param array $rows
   *
   * @return Sabel_DB_Model[]
   */
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
  
  /**
   * @return Sabel_DB_Model
   */
  public function save()
  {
    $args = func_get_args();
    return $this->prepare("save", $args)->execute();
  }
  
  /**
   * @return Sabel_DB_Model
   */
  protected function _save()
  {
    $new = MODEL($this->modelName);
    
    if ($this->isSelected()) {
      return $new->setProperties($this->_saveUpdate());
    } else {
      return $new->setProperties($this->_saveInsert());
    }
  }
  
  /**
   * @return array
   */
  protected function _saveInsert()
  {
    $columns = $this->getColumns();
    
    $saveValues = array();
    foreach ($this->values as $k => $v) {
      if (isset($columns[$k])) {
        $saveValues[$k] = $columns[$k]->cast($v);
      } else {
        $saveValues[$k] = $v;
      }
    }
    
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
  
  /**
   * @return array
   */
  protected function _saveUpdate()
  {
    if (($pkey = $this->schema->getPrimaryKey()) === null) {
      $message = "cannot update a model(there is not primary key).";
      throw new Sabel_DB_Exception($message);
    } else {
      if (is_string($pkey)) $pkey = array($pkey);
      
      foreach ($pkey as $key) {
        $this->setCondition($key, $this->__get($key));
      }
    }
    
    $stmt = $this->getStatement(Sabel_DB_Statement::UPDATE);
    
    $saveValues = array();
    foreach ($this->updateValues as $k => $v) {
      if (isset($columns[$k])) {
        $saveValues[$k] = $columns[$k]->cast($v);
      } else {
        $saveValues[$k] = $v;
      }
    }
    
    $this->prepareUpdate($stmt, $saveValues)->execute();
    return array_merge($this->values, $saveValues);
  }
  
  /**
   * @param array $values
   *
   * @return mixed
   */
  public function insert(array $values = null)
  {
    $args = func_get_args();
    return $this->prepare("insert", $args)->execute();
  }
  
  /**
   * @return mixed
   */
  protected function _insert()
  {
    @list ($data) = $this->arguments;
    
    $stmt = $this->getStatement(Sabel_DB_Statement::INSERT);
    return $this->prepareInsert($stmt, $data)->execute();
  }
  
  /**
   * @param array $values
   *
   * @return void
   */
  public function update(array $values = null)
  {
    $args = func_get_args();
    $this->prepare("update", $args)->execute();
  }
  
  /**
   * @return void
   */
  protected function _update()
  {
    @list ($data) = $this->arguments;
    $stmt = $this->getStatement(Sabel_DB_Statement::UPDATE);
    $this->prepareUpdate($stmt, $data)->execute();
  }
  
  /**
   * @param mixed $arg1
   * @param mixed $arg2
   *
   * @return void
   */
  public function delete($arg1 = null, $arg2 = null)
  {
    $args = func_get_args();
    $this->prepare("delete", $args)->execute();
  }
  
  /**
   * @return void
   */
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
  
  /**
   * @param int $type
   *
   * @return Sabel_DB_Abstract_Statement
   */
  public function getStatement($type)
  {
    $stmt = Sabel_DB_Driver::createStatement($this->connectionName);
    return $stmt->table($this->tableName)->type($type);
  }
  
  /**
   * @param Sabel_DB_Abstract_Statement $stmt
   *
   * @return Sabel_DB_Abstract_Statement
   */
  protected function prepareSelect(Sabel_DB_Abstract_Statement $stmt)
  {
    return $stmt->projection($this->projection)
                ->where($this->getCondition()->build($stmt))
                ->constraints($this->constraints);
  }
  
  /**
   * @param Sabel_DB_Abstract_Statement $stmt
   * @param array $values
   *
   * @return Sabel_DB_Abstract_Statement
   */
  protected function prepareUpdate(Sabel_DB_Abstract_Statement $stmt, $values = null)
  {
    if ($values === null) $values = $this->values;
    return $stmt->values($values)->where($this->getCondition()->build($stmt));
  }
  
  /**
   * @param Sabel_DB_Abstract_Statement $stmt
   * @param array $values
   *
   * @return Sabel_DB_Abstract_Statement
   */
  protected function prepareInsert(Sabel_DB_Abstract_Statement $stmt, $values = null)
  {
    if ($values === null) $values = $this->values;
    return $stmt->values($values)->sequenceColumn($this->schema->getSequenceColumn());
  }
  
  /**
   * @param Sabel_DB_Abstract_Statement $stmt
   *
   * @return Sabel_DB_Abstract_Statement
   */
  protected function prepareDelete(Sabel_DB_Abstract_Statement $stmt)
  {
    return $stmt->where($this->getCondition()->build($stmt));
  }
}
