<?php

/**
 * Sabel_DB_Model
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
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
  protected $tableName = "";
  
  /**
   * @var string
   */
  protected $modelName = "";
  
  /**
   * @var Sabel_DB_Statement
   */
  protected $statement = null;
  
  /**
   * @var Sabel_DB_Metadata_Table
   */
  protected $metadata = null;
  
  /**
   * @var Sabel_DB_Metadata_Column[]
   */
  protected $metaCols = array();
  
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
  protected $primaryKeyValues = array();
  
  /**
   * @var array
   */
  protected $values = array();
  
  /**
   * @var array
   */
  protected $updateValues = array();
  
  /**
   * @var string
   */
  protected $versionColumn = "";
  
  public function __construct($id = null)
  {
    $this->initialize();
    
    if ($id !== null) {
      $this->setCondition($id);
      $this->_doSelectOne($this);
    }
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
    
    $this->metadata = Sabel_DB_Metadata::getTableInfo($this->tableName, $this->connectionName);
    $this->metaCols = $this->metadata->getColumns();
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
   * @return self
   */
  public function setValues(array $values)
  {
    foreach ($values as $key => $val) {
      $this->__set($key, $val);
    }
    
    return $this;
  }
  
  /**
   * @param array $values
   *
   * @return self
   */
  public function setUpdateValues(array $values)
  {
    $this->updateValues = $values;
    
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
      if (isset($this->metaCols[$key])) {
        return $this->metaCols[$key]->cast($value);
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
    return array_keys($this->metaCols);
  }
  
  /**
   * @return Sabel_DB_Metadata_Table
   */
  public function getMetadata()
  {
    return $this->metadata;
  }
  
  /**
   * @return Sabel_DB_Metadata_Column[]
   */
  public function getColumns()
  {
    return $this->metaCols;
  }
  
  /**
   * @return array
   */
  public function toArray()
  {
    $columns = $this->metaCols;
    
    $retValues = array();
    foreach ($this->values as $k => $v) {
      $retValues[$k] = (isset($columns[$k])) ? $columns[$k]->cast($v) : $v;
    }
    
    return $retValues;;
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
   * @return self
   */
  public function setProperties(array $properties)
  {
    $pkey = $this->metadata->getPrimaryKey();
    $this->values = $properties;
    
    if (empty($pkey)) return;
    
    $this->selected = true;
    if (is_string($pkey)) $pkey = (array)$pkey;
    
    foreach ($pkey as $key) {
      if (!isset($properties[$key])) {
        $this->selected = false;
        break;
      }
    }
    
    if ($this->selected) {
      foreach ($pkey as $key) {
        $this->primaryKeyValues[$key] = $this->$key;
      }
    }
    
    return $this;
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
    $this->projection = array();
  }
  
  /**
   * @param mixed $projection
   *
   * @return self
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
    
    if ($arg2 !== null) {
      $condition->create($arg1, $arg2);
    } elseif (is_model($arg1)) {
      $joinkey = create_join_key($this, $arg1->getTableName());
      $colName = $this->getName() . "." . $joinkey["fkey"];
      $condition->create($colName, $arg1->$joinkey["id"]);
    } elseif (is_object($arg1)) {
      $condition->add($arg1);
    } else {
      $colName = $this->getName() . "." . $this->metadata->getPrimaryKey();
      $condition->create($colName, $arg1);
    }
  }
  
  /**
   * @param string $orderBy
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return self
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
   * @throws Sabel_Exception_InvalidArgument
   * @return self
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
   * @throws Sabel_Exception_InvalidArgument
   * @return self
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
    $result = null;
    if ($this->hasMethod("beforeSelect")) {
      $result = $this->beforeSelect();
    }
    
    if ($result === null) {
      $this->setCondition($arg1, $arg2);
      $projection = $this->projection;
      $this->projection = "COUNT(*) AS cnt";
      
      $stmt = $this->prepareStatement(Sabel_DB_Statement::SELECT);
      $rows = $this->prepareSelect($stmt)->execute();
      
      $this->projection = $projection;
      $result = (int)$rows[0]["cnt"];
    }
    
    if ($this->hasMethod("afterSelect")) {
      $afterResult = $this->afterSelect($result);
      if ($afterResult !== null) $result = $afterResult;
    }
    
    if ($this->autoReinit) $this->initState();
    
    return $result;
  }
  
  /**
   * @param mixed $arg1
   * @param mixed $arg2
   *
   * @return Sabel_DB_Model
   */
  public function selectOne($arg1 = null, $arg2 = null)
  {
    $result = null;
    if ($this->hasMethod("beforeSelect")) {
      $result = $this->beforeSelect();
    }
    
    if ($result === null) {
      if ($arg1 === null && $this->condition === null) {
        $message = __METHOD__ . "() must set the condition.";
        throw new Sabel_DB_Exception($message);
      }
      
      $this->setCondition($arg1, $arg2);
      $model = MODEL($this->modelName);
      $this->_doSelectOne($model);
      $result = $model;
    }
    
    if ($this->hasMethod("afterSelect")) {
      $afterResult = $this->afterSelect($result);
      if ($afterResult !== null) $result = $afterResult;
    }
    
    if ($this->autoReinit) $this->initState();
    
    return $result;
  }
  
  /**
   * @param mixed $arg1
   * @param mixed $arg2
   *
   * @return Sabel_DB_Model[]
   */
  public function select($arg1 = null, $arg2 = null)
  {
    $result = null;
    if ($this->hasMethod("beforeSelect")) {
      $result = $this->beforeSelect();
    }
    
    if ($result === null) {
      $this->setCondition($arg1, $arg2);
      $stmt = $this->prepareStatement(Sabel_DB_Statement::SELECT);
      $rows = $this->prepareSelect($stmt)->execute();
      $result = (empty($rows)) ? array() : $this->toModels($rows);
    }
    
    if ($this->hasMethod("afterSelect")) {
      $afterResult = $this->afterSelect($result);
      if ($afterResult !== null) $result = $afterResult;
    }
    
    if ($this->autoReinit) $this->initState();
    
    return $result;
  }
  
  /**
   * @param array $additionalValues
   *
   * @return int
   */
  public function save(array $additionalValues = array())
  {
    if ($this->isSelected()) {
      $this->updateValues = array_merge($this->updateValues, $additionalValues);
      $result = $this->saveUpdate();
    } else {
      $this->values = array_merge($this->values, $additionalValues);
      $result = $this->saveInsert();
    }
    
    if ($this->autoReinit) $this->initState();
    
    return $result;
  }
  
  /**
   * @return array
   */
  protected function saveInsert()
  {
    $result = null;
    if ($this->hasMethod("beforeInsert")) {
      $result = $this->beforeInsert();
    }
    
    if ($result === null) {
      $columns = $this->getColumns();
      $saveValues = array();
      
      foreach ($this->values as $k => $v) {
        $saveValues[$k] = (isset($columns[$k])) ? $columns[$k]->cast($v) : $v;
      }
      
      $stmt  = $this->prepareStatement(Sabel_DB_Statement::INSERT);
      $newId = $this->prepareInsert($stmt, $saveValues)->execute();
      
      foreach ($columns as $name => $column) {
        if (!array_key_exists($name, $saveValues)) {
          $this->$name = $column->default;
        }
      }
      
      if ($newId !== null && ($field = $this->metadata->getSequenceColumn()) !== null) {
        $this->$field = $newId;
      }
      
      $result = 1;
    }
    
    if ($this->hasMethod("afterInsert")) {
      $afterResult = $this->afterInsert($result);
      if ($afterResult !== null) $result = $afterResult;
    }
    
    return $result;
  }
  
  /**
   * @throws Sabel_DB_Exception
   * @return array
   */
  protected function saveUpdate()
  {
    $result = null;
    if ($this->hasMethod("beforeUpdate")) {
      $result = $this->beforeUpdate();
    }
    
    if ($result === null) {
      if (($pkey = $this->metadata->getPrimaryKey()) === null) {
        $message = __METHOD__ . "() can't update a model(there is not primary key).";
        throw new Sabel_DB_Exception($message);
      }
      
      foreach ((is_string($pkey)) ? array($pkey) : $pkey as $key) {
        if ($this->primaryKeyValues[$key] === $this->$key) {
          $this->setCondition("{$this->modelName}.{$key}", $this->__get($key));
        } else {
          $message = __METHOD__ . "() can't update the primary key value.";
          throw new Sabel_DB_Exception($message);
        }
      }
      
      $saveValues = array();
      foreach ($this->updateValues as $k => $v) {
        $saveValues[$k] = (isset($columns[$k])) ? $columns[$k]->cast($v) : $v;
      }
      
      $vColumn = ($this->versionColumn === "") ? "version" : $this->versionColumn;
      
      if (isset($this->metaCols[$vColumn])) {
        $_column = $this->metaCols[$vColumn];
        $currentVersion = $this->__get($vColumn);
        $this->setCondition("{$this->modelName}.{$vColumn}", $currentVersion);
        
        if ($_column->isInt()) {
          $saveValues[$vColumn] = $currentVersion + 1;
        } elseif ($_column->isDatetime()) {
          $saveValues[$vColumn] = now();
        } else {
          $message = __METHOD__ . "() version column must be DATETIME or INT.";
          throw new Sabel_DB_Exception($message);
        }
      }
      
      $this->updateValues = array();
      $stmt = $this->prepareStatement(Sabel_DB_Statement::UPDATE);
      $result = $this->prepareUpdate($stmt, $saveValues)->execute();
      
      if (isset($this->metaCols[$vColumn]) && $result < 1) {
        $message = __METHOD__ . "() this model has already been changed by other transactions.";
        throw new Sabel_DB_Exception_StaleModel($message);
      }
    }
    
    if ($this->hasMethod("afterUpdate")) {
      $afterResult = $this->afterUpdate($result);
      if ($afterResult !== null) $result = $afterResult;
    }
    
    return $result;
  }
  
  /**
   * @param array $values
   *
   * @return mixed
   */
  public function insert(array $values = array())
  {
    $result = null;
    if ($this->hasMethod("beforeInsert")) {
      $result = $this->beforeInsert();
    }
    
    if ($result === null) {
      $stmt   = $this->prepareStatement(Sabel_DB_Statement::INSERT);
      $result = $this->prepareInsert($stmt, $values)->execute();
    }
    
    if ($this->hasMethod("afterInsert")) {
      $afterResult = $this->afterInsert($result);
      if ($afterResult !== null) $result = $afterResult;
    }
    
    if ($this->autoReinit) $this->initState();
    
    return $result;
  }
  
  /**
   * @param array $values
   *
   * @return int
   */
  public function update(array $values = array())
  {
    $result = null;
    if ($this->hasMethod("beforeUpdate")) {
      $result = $this->beforeUpdate();
    }
    
    if ($result === null) {
      $stmt   = $this->prepareStatement(Sabel_DB_Statement::UPDATE);
      $result = $this->prepareUpdate($stmt, $values)->execute();
    }
    
    if ($this->hasMethod("afterUpdate")) {
      $afterResult = $this->afterUpdate($result);
      if ($afterResult !== null) $result = $afterResult;
    }
    
    if ($this->autoReinit) $this->initState();
    
    return $result;
  }
  
  /**
   * @param mixed $arg1
   * @param mixed $arg2
   *
   * @return int
   */
  public function delete($arg1 = null, $arg2 = null)
  {
    $result = null;
    if ($this->hasMethod("beforeDelete")) {
      $result = $this->beforeDelete();
    }
    
    if ($result === null) {
      $condition = $this->getCondition();
      if (!$this->isSelected() && $arg1 === null && $condition->isEmpty()) {
        $stmt   = $this->prepareStatement(Sabel_DB_Statement::DELETE);
        $result = $this->prepareDelete($stmt)->execute();
      } else {
        if ($arg1 !== null) {
          $this->setCondition($arg1, $arg2);
        } elseif ($this->isSelected()) {
          if (($pkey = $this->metadata->getPrimaryKey()) === null) {
            $message = __METHOD__ . "() cannot delete model(there is not primary key).";
            throw new Sabel_DB_Exception($message);
          } else {
            foreach ((is_string($pkey)) ? array($pkey) : $pkey as $key) {
              $this->setCondition($this->modelName . "." . $key, $this->__get($key));
            }
          }
        }
        
        $stmt   = $this->prepareStatement(Sabel_DB_Statement::DELETE);
        $result = $this->prepareDelete($stmt)->execute();
      }
    }
    
    if ($this->hasMethod("afterDelete")) {
      $afterResult = $this->afterDelete($result);
      if ($afterResult !== null) $result = $afterResult;
    }
    
    if ($this->autoReinit) $this->initState();
    
    return $result;
  }
  
  /**
   * @param const $type Sabel_DB_Statement
   *
   * @return Sabel_DB_Statement
   */
  public function prepareStatement($type = Sabel_DB_Statement::QUERY)
  {
    $stmt = Sabel_DB::createStatement($this->connectionName);
    return $stmt->setMetadata($this->metadata)->type($type);
  }
  
  /**
   * @param Sabel_DB_Statement $stmt
   *
   * @return Sabel_DB_Statement
   */
  protected function prepareSelect(Sabel_DB_Statement $stmt)
  {
    return $stmt->projection($this->projection)
                ->where($this->getCondition()->build($stmt))
                ->constraints($this->constraints);
  }
  
  /**
   * @param Sabel_DB_Statement $stmt
   * @param array $values
   *
   * @return Sabel_DB_Statement
   */
  protected function prepareUpdate(Sabel_DB_Statement $stmt, array $values = array())
  {
    if (empty($values)) $values = $this->values;
    return $stmt->values($values)->where($this->getCondition()->build($stmt));
  }
  
  /**
   * @param Sabel_DB_Statement $stmt
   * @param array $values
   *
   * @return Sabel_DB_Statement
   */
  protected function prepareInsert(Sabel_DB_Statement $stmt, array $values = array())
  {
    if (empty($values)) $values = $this->values;
    return $stmt->values($values)->sequenceColumn($this->metadata->getSequenceColumn());
  }
  
  /**
   * @param Sabel_DB_Statement $stmt
   *
   * @return Sabel_DB_Statement
   */
  protected function prepareDelete(Sabel_DB_Statement $stmt)
  {
    return $stmt->where($this->getCondition()->build($stmt));
  }
  
  /**
   * @param Sabel_DB_Model $model
   *
   * @return void
   */
  protected function _doSelectOne(Sabel_DB_Model $model)
  {
    $stmt = $this->prepareStatement(Sabel_DB_Statement::SELECT);
    $rows = $this->prepareSelect($stmt)->execute();
    if (isset($rows[0])) $model->setProperties($rows[0]);
  }
  
  /**
   * @param array $rows
   *
   * @return Sabel_DB_Model[]
   */
  protected function toModels(array $rows)
  {
    $results = array();
    $source  = MODEL($this->modelName);
    
    foreach ($rows as $row) {
      $model = clone $source;
      $model->setProperties($row);
      $results[] = $model;
    }
    
    return $results;
  }
}
