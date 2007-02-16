<?php

Sabel::using('Sabel_DB_Type_Const');

Sabel::using('Sabel_ValueObject');
Sabel::using('Sabel_DB_SimpleCache');

Sabel::using('Sabel_DB_Condition');

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
  const UPDATE_TIME_COLUMN = 'auto_update';
  const CREATE_TIME_COLUMN = 'auto_create';
  const DATETIME_FORMAT    = 'Y-m-d H:i:s';

  private
    $data      = array(),
    $columns   = array(),
    $newData   = array(),
    $selected  = false;

  private
    $schema    = null,
    $relation  = null,
    $tableProp = null,
    $sColumns  = array();

  private
    $parentModels    = array(),
    $acquiredParents = array(),
    $dbTables        = array(),
    $joinPairBuffer  = array(),
    $joinCondBuffer  = array(),
    $cascadeStack    = array();

  protected
    $errors      = null,
    $projection  = '*',
    $conditions  = array(),
    $constraints = array();

  protected
    $table       = '',
    $connectName = 'default',
    $structure   = 'normal',
    $localize    = array(),
    $parents     = array(),
    $children    = array();

  protected
    $selectConditions = array(),
    $childConditions  = array(),
    $childConstraints = array();

  protected
    $validateIgnores  = array(),
    $validateOnInsert = false,
    $validateOnUpdate = false;

  protected
    $ignoreNothingPrimary = false,
    $ignoreEmptyParent    = false;

  protected
    $validateMessages = array('length'   => 'is too long',
                              'maximum'  => 'is too large',
                              'nullable' => 'should not be a blank',
                              'type'     => 'invalid data type');

  public function __construct($param1 = null, $param2 = null)
  {
    $this->initialize();
    if (!empty($param1)) $this->defaultSelectOne($param1, $param2);
  }

  protected function initialize($mdlName = null)
  {
    $mdlName = ($mdlName === null)  ? get_class($this) : $mdlName;
    $tblName = ($this->table === '') ? convert_to_tablename($mdlName) : $this->table;
    $cache   = Sabel_DB_SimpleCache::get('schema_' . $tblName);

    if ($cache) {
      $this->schema  = $cache;
      $this->columns = Sabel_DB_SimpleCache::get('columns_' . $tblName);
      $props         = Sabel_DB_SimpleCache::get('props_'   . $tblName);
    } else {
      $sClsName = 'Schema_' . $mdlName;
      Sabel::using($sClsName);

      if (class_exists($sClsName, false)) {
        list($tblSchema, $props) = $this->getSchemaFromCls($sClsName, $tblName);
      } else {
        list($tblSchema, $props) = $this->getSchemaFromDb($tblName);
      }

      $props['table']       = $tblName;
      $props['connectName'] = $this->connectName;
      $columns = array_keys($tblSchema->getColumns());

      Sabel_DB_SimpleCache::add('schema_'  . $tblName, $tblSchema);
      Sabel_DB_SimpleCache::add('columns_' . $tblName, $columns);
      Sabel_DB_SimpleCache::add('props_'   . $tblName, $props);

      if ($props['primaryKey'] === null) {
        if (!$this->ignoreNothingPrimary && $this->structure !== 'view') {
          trigger_error('primary key not found in ' . $props['table'], E_USER_NOTICE);
        }
      }

      $this->schema  = $tblSchema;
      $this->columns = $columns;
    }

    $this->tableProp = new Sabel_ValueObject($props);
  }

  /**
   * create driver instance.
   *
   * @return object
   */
  public function getDriver()
  {
    $driver = Sabel_DB_Connection::getDriver($this->getConnectName());
    $driver->extension($this->tableProp);

    return $driver;
  }

  public function __set($key, $val)
  {
    $this->data[$key] = $val;

    if ($this->selected && in_array($key, $this->columns)) {
      $this->newData[$key] = $val;
    }
  }

  public function setProperties($row)
  {
    if (!is_array($row))
      throw new Exception('Error: setProperties() argument should be an array.');

    foreach ($row as $key => $val) $this->data[$key] = $val;
  }

  /**
   * set table name.
   *
   * @param string $tblName
   * @return void
   */
  public function setTableName($tblName)
  {
    $this->tableProp->table = $tblName;
  }

  /**
   * returns the table name.
   *
   * @return string
   */
  public function getTableName()
  {
    return $this->tableProp->table;
  }

  /**
   * returns the primary key(s).
   *
   * @return mixed string or array
   */
  public function getPrimaryKey()
  {
    return $this->tableProp->primaryKey;
  }

  /**
   * set primary key
   *
   * @param  mixed $keyName string or array ( joint keys ).
   * @return void
   */
  public function setPrimaryKey($keyName)
  {
    $this->tableProp->primaryKey = $keyName;
  }

  /**
   * returns the increment key ( sequence column ).
   *
   * @return string
   */
  public function getIncrementKey()
  {
    return $this->tableProp->incrementKey;
  }

  /**
   * returns the connection name.
   *
   * @return string
   */
  public function getConnectName()
  {
    return $this->tableProp->connectName;
  }

  /**
   * set ( choice ) connection name.
   *
   * @param  string $connectName
   * @return void
   */
  public function setConnectName($connectName)
  {
    $this->tableProp->connectName = $connectName;
  }

  /**
   * set projection ( columns ) for SELECT.
   *
   * @param  mixed $p column names. array('col1, 'col2', ...) or string 'col1, col2, ...'
   * @return void
   */
  public function setProjection($p)
  {
    $this->projection = (is_array($p)) ? join(',', $p) : $p;
  }

  /**
   * get projection ( columns ).
   *
   * @return string
   */
  public function getProjection()
  {
    return $this->projection;
  }

  public function setParents($parents)
  {
    if (!is_array($parents))
      throw new Exception('Error: setParents() argument should be an array.');

    $this->parents = $parents;
  }

  public function setChildren($children)
  {
    if (!is_array($children))
      throw new Exception('Error: setChildren() argument should be an array.');

    $this->children = $children;
  }

  /**
   * setting condition.
   *
   * @param mixed    $arg1 column name ( with the condition prefix ),
   *                       or value of primary key,
   *                       or array condition(s),
   *                       or instance of Sabel_DB_Condition.
   * @param mixed    $arg2 condition value.
   * @param constant $arg3 denial ( Sabel_DB_Condition::NOT )
   * @return void
   */
  public function setCondition($arg1, $arg2 = null, $arg3 = null)
  {
    if (empty($arg1)) return null;

    if ($arg1 instanceof Sabel_DB_Condition) {
      $this->conditions[$arg1->key] = $arg1;
    } elseif (is_array($arg1)) {
      $tmp = array_values($arg1);
      if (is_object($tmp[0])) {
        $this->conditions[] = $arg1;
      } else {
        foreach ($arg1 as $key => $val) {
          $condition = new Sabel_DB_Condition($key, $val);
          $this->conditions[$condition->key] = $condition;
        }
      }
    } else {
      if ($arg2 === null) {
        $pKey = $this->tableProp->primaryKey;
        if (is_array($pKey)) {
          throw new Exception('Error:setCondition() please specify a column for the condition.');
        }
        $arg3 = null;
        $arg2 = $arg1;
        $arg1 = $pKey;
      }

      $condition = new Sabel_DB_Condition($arg1, $arg2, $arg3);
      $this->conditions[$condition->key] = $condition;
    }
  }

  /**
   * unset condition. or with constraint.
   *
   * @param  boolean $with unset with constraint
   * @return void
   */
  public function unsetCondition($with = false)
  {
    $this->conditions = array();
    if ($with) $this->unsetConstraint();
  }

  public function setChildCondition($arg1, $arg2 = null, $arg3 = null)
  {
    if (is_object($arg1) || is_array($arg1)) {
      $this->childConditions[] = $arg1;
    } else {
      Sabel::using('Sabel_DB_Condition');
      $condition = new Sabel_DB_Condition($arg1, $arg2, $arg3);
      $this->childConditions[$condition->key] = $condition;
    }
  }

  /**
   * setting constraint.
   * the keys which you can use are 'group', 'having', 'order', 'limit', 'offset'.
   *
   * @param  mixed $arg1 array constraint(s). or string key.
   * @param  mixed $arg2 value of integer or string.
   * @return void
   */
  public function setConstraint($arg1, $arg2 = null)
  {
    if (!is_array($arg1)) $arg1 = array($arg1 => $arg2);

    foreach ($arg1 as $key => $val) {
      if (!isset($val)) continue;

      if (strpos($val, '.') !== false) {
        list($mdlName, $val) = explode('.', $val);
        $val = convert_to_tablename($mdlName) . '.' . $val;
      }

      $this->constraints[$key] = $val;
    }
  }

  /**
   * unset constraint.
   *
   * @return void
   */
  public function unsetConstraint()
  {
    $this->constraints = array();
  }

  public function setChildConstraint($mdlName, $constraints)
  {
    if (!is_array($constraints))
      throw new Exception('Error:setChildConstraint() second argument must be an array.');

    foreach ($constraints as $key => $val) {
      if (strpos($val, '.') !== false) {
        list($mdlName, $val) = explode('.', $val);
        $val = convert_to_tablename($mdlName) . '.' . $val;
      }
      $this->childConstraints[$mdlName][$key] = $val;
    }
  }

  public function __get($key)
  {
    if (!isset($this->data[$key])) return null;
    return $this->convertData($key, $this->data[$key]);
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
    foreach ($this->data as $key => $val) {
      if (in_array($key, $this->columns)) $real[$key] = $this->convertData($key, $val);
    }
    return $real;
  }

  public function toArray()
  {
    return $this->data;
  }

  public function getData()
  {
    return $this->data;
  }

  public function getLocalizedName($name)
  {
    return (isset($this->localize[$name])) ? $this->localize[$name] : $name;
  }

  public function getStructure()
  {
    return $this->structure;
  }

  public function getMyParents()
  {
    return $this->parents;
  }

  public function getMyChildren()
  {
    return $this->children;
  }

  public function isSelected()
  {
    return $this->selected;
  }

  public function getChildConstraint()
  {
    return $this->childConstraints;
  }

  public function getChildCondition()
  {
    return $this->childConditions;
  }

  /**
   * unset condition for child. or with constraint.
   *
   * @param  boolean $with unset with constraint
   * @return void
   */
  public function unsetChildCondition($with)
  {
    $this->childConditions = array();
    if ($with) $this->unsetChildConstraint();
  }

  public function unsetChildConstraint()
  {
    $this->childConstraints = array();
  }

  public function validateOnInsert($bool)
  {
    $this->validateOnInsert = $bool;
  }

  public function validateOnUpdate($bool)
  {
    $this->validateOnUpdate = $bool;
  }

  public function addValidateIgnore($cols)
  {
    if (is_string($cols)) $cols = (array)$cols;
    foreach ($cols as $col) $this->validateIgnores[] = $col;
  }

  public function schema($tblName = null)
  {
    if (isset($tblName)) return $this->getTableSchema($tblName)->getColumns();

    $columns = $this->schema->getColumns();
    foreach ($this->data as $name => $value) {
      if (isset($columns[$name])) {
        $columns[$name]->value = $this->convertData($name, $value);
      }
    }

    return $columns;
  }

  public function hasError()
  {
    return (is_object($this->errors)) ? $this->errors->hasError() : false;
  }

  public function getErrors()
  {
    return $this->errors;
  }

  public function getTableNames()
  {
    return $this->createSchemaAccessor()->getTableNames();
  }

  public function getColumnNames($tblName = null)
  {
    if ($tblName === null) {
      return $this->columns;
    } else {
      return $this->createSchemaAccessor()->getColumnNames($tblName);
    }
  }

  public function getTableSchema($tblName = null)
  {
    if ($tblName === null) {
      return $this->schema;
    } else {
      return $this->createSchemaAccessor()->getTable($tblName);
    }
  }

  public function getAllTableSchema()
  {
    return $this->createSchemaAccessor()->getTables();
  }

  public function getTableEngine()
  {
    return $this->tableProp->tableEngine;
  }

  protected function createSchemaAccessor()
  {
    $connectName = $this->getConnectName();
    $schemaName  = Sabel_DB_Connection::getSchema($connectName);
    return Sabel::load('Sabel_DB_Schema_Accessor', $connectName, $schemaName);
  }

  /**
   * start transaction.
   *
   * @return void
   */
  public function begin()
  {
    $this->getDriver()->begin($this->getConnectName());
  }

  /**
   * an alias for begin() and addTransaction()
   *
   * @return void
   */
  public function startTransaction()
  {
    $this->begin();
  }

  /**
   * an alias for begin() and startTransaction()
   *
   * Example :
   * <code>
   *   $model1->startTransaction();  // or    $model1->begin();
   *   $model2->addTransaction();    // equal $model2->begin();
   *     .
   *     .
   *     .
   *   $model1->commit();
   * </code>
   */
  public function addTransaction()
  {
    $this->begin();
  }

  public function commit()
  {
    $this->getDriver()->commit();
  }

  public function rollback()
  {
    $this->getDriver()->rollback();
  }

  public function close()
  {
    Sabel_DB_Connection::close($this->getConnectName());
  }

  public function getFirst($orderColumn)
  {
    return $this->getEdge('ASC', $orderColumn);
  }

  public function getLast($orderColumn)
  {
    return $this->getEdge('DESC', $orderColumn);
  }

  protected function getEdge($order, $orderColumn)
  {
    $this->setCondition($orderColumn, Sabel_DB_Condition::NOTNULL);
    $this->setConstraint(array('limit' => 1, 'order' => "$orderColumn $order"));
    $result = $this->selectOne();
    unset($this->constraints['limit']);
    unset($this->constraints['order']);
    return $result;
  }

  /**
   * get rows count.
   *
   * @param  mixed    $param1 column name ( with the condition prefix ), or value of primary key.
   * @param  mixed    $param2 condition value.
   * @param  constant $param3 denial ( Sabel_DB_Condition::NOT )
   * @return integer rows count
   */
  public function getCount($arg1 = null, $arg2 = null, $arg3 = null)
  {
    if (array_diff(array_keys($this->constraints), array('limit'))) {
      throw new Exception("can't use ORDER HAVING OFFSET GROUP in count() context.");
    }

    $this->setCondition($arg1, $arg2, $arg3);

    $tmpProjection = $this->projection;
    $tmpConstraint = $this->constraints;

    $this->projection  = 'count(*)';
    $this->constraints = array('limit' => 1);

    $row = $this->doSelect()->fetch(Sabel_DB_Result_Row::NUM);

    $this->projection = $tmpProjection;
    unset($this->constraints['limit']);
    return (int)$row[0];
  }

  /**
   * called from construct.
   * this method is almost the same as selectOne().
   *
   * @return object
   */
  protected function defaultSelectOne($param1, $param2 = null)
  {
    $this->setCondition($param1, $param2);
    $this->createModel($this);
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
    return $this->createModel(clone $this);
  }

  protected function createModel($model)
  {
    if ($row = $model->doSelect()->fetch()) {
      if ($this->parents) {
        $row = $this->addParent($row, $model);
      }
      $model->transrate($row);
      $model->getDefaultChild($model);
    } else {
      $model->selectConditions = $model->conditions;
      foreach ($model->conditions as $condition) {
        $model->{$condition->key} = $condition->value;
      }
    }
    return $model;
  }

  public function doSelect($query = null)
  {
    $driver  = $this->getDriver();
    $tblName = $this->getTableName();

    try {
      if ($query === null) {
        $driver->select($tblName, $this->getProjection(), $this->conditions, $this->constraints);
      } else {
        $driver->selectQuery($query, $this->conditions, $this->constraints);
      }
      return $driver->getResultSet();
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $driver);
    }
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
    $tblName = $this->tableProp->table;
    $parents = $this->parents;

    if ($parents) {
      $this->relation = Sabel::load('Sabel_DB_Model_Relation');
      $this->dbTables = $this->getTableNames();
      $this->relation->setColumns($tblName, $this->columns);

      if ($this->addRelationalDataToBuffer($tblName, $parents)) {
        return $this->automaticJoin();
      }
    }

    $resultSet = $this->doSelect();
    if ($resultSet->isEmpty()) return false;

    $models = array();
    $ccond  = $this->getChildConstraint();
    $obj    = MODEL(convert_to_modelname($tblName));
    $rows   = $resultSet->fetchAll();

    foreach ($rows as $row) {
      $model = clone $obj;

      if ($ccond)   $model->childConstraints = $ccond;
      if ($parents) $row = $this->addParent($row, $this);

      $model->transrate($row);
      $this->getDefaultChild($model);
      $models[] = $model;
    }
    return $models;
  }

  protected function isSameConnectionNames($tblName, $alias = null)
  {
    if (!in_array($tblName, $this->dbTables)) return false;
    $model = MODEL(convert_to_modelname($tblName));

    if ($model->parents) {
      $result = $this->addRelationalDataToBuffer($tblName, $model->parents);
      if (!$result) return false;
    }
    
    if ($alias !== null) $tblName = $alias;
    $this->relation->setColumns($tblName, $model->columns);
    return true;
  }

  private function addRelationalDataToBuffer($mdlName, $parents)
  {
    $pairBuf = array();
    $condBuf = array();
    $tblName = convert_to_tablename($mdlName);

    foreach ($parents as $parent) {
      $res   = $this->relation->toRelationPair($mdlName, $parent);
      $ptbl  = $res['ptable'];
      $alias = ($res['alias']) ? convert_to_tablename($res['alias']) : null;
      if ($this->isSameConnectionNames($ptbl, $alias)) {
        if ($alias === null) {
          $key  = $ptbl;
          $cond = $res['parent'];
        } else {
          $key  = $ptbl . ' AS ' . $alias;
          $cond = $alias . '.' . $res['pkey'];
          $this->relation->setAlias($alias, $ptbl);
        }
        
        $condBuf[] = array($key, "{$res['child']} = $cond");
        $pairBuf[] = array($tblName, ($alias) ? $alias : $ptbl);
      } else {
        return false;
      }
    }

    if ($pairBuf) $this->joinPairBuffer[] = $pairBuf;
    if ($condBuf) $this->joinCondBuffer[] = $condBuf;
    return true;
  }


  protected function automaticJoin()
  {
    foreach (array_reverse($this->joinPairBuffer) as $pair) {
      foreach ($pair as $p) {
        $this->relation->setParent($p[0], $p[1]);
        $this->relation->setTablePair($p[0], $p[1]);
      }
    }

    foreach (array_reverse($this->joinCondBuffer) as $cond) {
      foreach ($cond as $c) $this->relation->setCondition($c[0], $c[1]);
    }

    return $this->relation->execJoin($this);
  }

  protected function addParent($row, $model)
  {
    $this->acquiredParents = array($this->tableProp->table);
    if ($this->relation === null) $this->relation = Sabel::load('Sabel_DB_Model_Relation');

    foreach ($model->parents as $parent) {
      $res = $this->relation->toRelationPair($model->tableProp->table, $parent);
      if (isset($row["{$res['ckey']}"])) {
        $ptbl = $res['ptable'];
        $row[$ptbl] = $this->createParentModel($ptbl, $res['pkey'], $row["{$res['ckey']}"]);
      }
    }

    return $row;
  }

  private function createParentModel($tblName, $idCol, $id)
  {
    if ($this->structure !== 'tree' && $this->isAcquired($tblName)) return false;

    if (isset($this->parentModels[$tblName])) {
      $model = clone $this->parentModels[$tblName];
    } else {
      $model = MODEL(convert_to_modelname($tblName));
      $this->parentModels[$tblName] = $model;
    }

    $cacheName = $tblName . $id;
    if (!is_array($row = Sabel_DB_SimpleCache::get($cacheName))) {
      $model->setCondition($idCol, $id);
      $resultSet = $model->doSelect();

      if (!($row = $resultSet->fetch()) && !$this->ignoreEmptyParent) {
        $msg = "Error: relational error. parent '{$tblName}' does not exist. "
             . "Please set ignoreEmpryParent = true if you want to ignore it.";

        throw new Exception($msg);
      }

      Sabel_DB_SimpleCache::add($cacheName, $row);
    }

    if (!empty($model->parents)) {
      $row = $this->addParent($row, $model);
    }

    $model->transrate($row);
    return $model;
  }

  private function isAcquired($tblName)
  {
    if (in_array($tblName, $this->acquiredParents)) {
      return true;
    } else {
      $this->acquiredParents[] = $tblName;
      return false;
    }
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

  /**
   * fetch the children by relating own primary key to foreign key of a given table name.
   *
   * @param  string $child model name.
   * @param  mixed  $model not used. ( used internally )
   * @return array
   */
  public function getChild($child, $model = null)
  {
    if ($model === null) $model = $this;

    $pair = $child;
    if (strpos($child, ':') !== false) {
      list ($child) = explode(':', $child);
    } else {
      $pair = $child . ':' . convert_to_modelname($model->tableProp->table);
    }

    list ($child) = explode('.', $child);
    $res  = Sabel::load('Sabel_DB_Model_Relation')->toRelationPair($child, $pair);
    $pkey = $res['pkey'];

    $cModel = MODEL($child);
    $this->chooseChildConstraint($child, $model);
    $model->setChildCondition($res['ckey'], $model->$pkey);

    $cModel->conditions = $model->getChildCondition();
    $cconst = $model->getChildConstraint();
    if (isset($cconst[$child])) $cModel->constraints = $cconst[$child];

    $resultSet = $cModel->doSelect();
    if ($resultSet->isEmpty()) return $model->$child = false;

    $children = array();
    $childObj = MODEL($child);
    $rows     = $resultSet->fetchAll();

    foreach ($rows as $row) {
      $childObj = clone $childObj;
      if (!empty($childObj->parents)) {
        $row = $this->addParent($row, $childObj);
      }
      $childObj->transrate($row);
      $this->getDefaultChild($childObj);
      $children[] = $childObj;
    }

    $model->$child = $children;
    return $children;
  }

  protected function getDefaultChild($model)
  {
    if ($children = $model->getMyChildren()) {
      foreach ($children as $child) {
        $this->chooseChildConstraint($child, $model);
        $model->getChild($child, $model);
      }
    }
  }

  protected function chooseChildConstraint($child, $model)
  {
    $constraints = array();
    $thisCConst  = $this->getChildConstraint();
    $modelCConst = $model->getChildConstraint();

    if (isset($thisCConst[$child])) {
      $constraints = $thisCConst[$child];
    } elseif (isset($modelCConst[$child])) {
      $constraints = $modelCConst[$child];
    }

    if ($constraints) $model->setChildConstraint($child, $constraints);

    if ($thisCConst)  {
      foreach ($thisCConst as $cldName => $param) {
        $model->setChildConstraint($cldName, $param);
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
    if (!is_array($pKey)) $pKey = (array)$pKey;

    foreach ($pKey as $key) {
      if (isset($row[$key])) {
        $condition = new Sabel_DB_Condition($key, $row[$key]);
        $this->selectConditions[$key] = $condition;
      }
    }

    $this->setProperties($row);
    $this->selected = true;
  }

  public function newChild($child = null)
  {
    $pKey = $this->getPrimaryKey();
    $id   = $this->$pKey;

    if (empty($id)) {
      throw new Exception("Error:newChild() who is a parent? hasn't id value.");
    }

    $parent  = $this->getTableName();
    $tblName = ($child === null) ? $parant : $child;
    $model   = MODEL(convert_to_modelname($tblName));
    $column  = "{$parent}_{$pKey}";
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
    $pkey = $this->getPrimaryKey();

    if (isset($this->data[$pkey])) {
      $id = $this->data[$pkey];
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

      $saveData = ($data) ? $data : $this->newData;
      $this->recordTime($saveData, $tblName, self::UPDATE_TIME_COLUMN);
      $this->conditions = $this->selectConditions;
      $this->doUpdate($saveData);
      $newData = array_merge($this->getRealData(), $saveData);
      $this->newData = array();
    } else {
      if ($this->validateOnInsert) {
        if (($this->errors = $this->validate())) return false;
      }

      $newData = ($data) ? $data : $this->data;

      $this->recordTime($newData, $tblName, self::UPDATE_TIME_COLUMN);
      $this->recordTime($newData, $tblName, self::CREATE_TIME_COLUMN);

      $incCol = $this->getIncrementKey();
      $newId  = $this->doInsert($newData, $incCol);

      if ($incCol) {
        if (!isset($newData[$incCol])) $newData[$incCol] = $newId;
      }
    }

    foreach ($newData as $key => $val) $newModel->$key = $val;

    $newModel->selected = true;
    return $newModel;
  }

  public function doUpdate($data)
  {
    try {
      $driver = $this->getDriver();
      $driver->update($this->tableProp->table, $data, $this->conditions);
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $driver);
    }
  }

  public function doInsert($data, $incCol = null)
  {
    try {
      $driver = $this->getDriver();
      $driver->insert($this->tableProp->table, $data, $incCol);
      return $driver->getLastInsertId();
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $driver);
    }
  }

  protected function recordTime(&$data, $tblName, $colName)
  {
    if (in_array($colName, $this->columns)) {
      if (!isset($data[$colName])) $data[$colName] = date(self::DATETIME_FORMAT);
    }
  }

  public function validate()
  {
    $this->sColumns = $this->schema->getColumns();

    if (is_object($this->errors)) {
      $errors = $this->errors;
    } else {
      $this->errors = $errors = Sabel::load('Sabel_Errors');
    }

    $dataForValidate = ($this->isSelected()) ? $this->newData : $this->data;

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
      $method = (extension_loaded('mbstring')) ? 'mb_strlen' : 'strlen';
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

  public function multipleInsert($data)
  {
    if (!is_array($data)) {
      throw new Exception('Error:multipleInsert() data is not array.');
    }

    try {
      $driver = $this->getDriver();
      $incCol = $this->getIncrementKey();

      $this->begin();
      $driver->arrayInsert($this->tableProp->table, $data, $incCol);
      $this->commit();
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * delete row(s)
   *
   * @param  mixed     $param1 column name ( with the condition prefix ), or value of primary key.
   * @param  mixed     $param2 condition value.
   * @param  constrant $param3 denial ( Sabel_DB_Condition::NOT )
   * @return void
   */
  public function remove($param1 = null, $param2 = null, $param3 = null)
  {
    if ($this->structure === 'view')
      throw new Exception('Error: delete command cannot be executed to view.');

    if ($param1 === null && empty($this->conditions)) {
      $pKey    = $this->tableProp->primaryKey;
      $scond   = $this->selectConditions;
      $idValue = (isset($scond[$pKey])) ? $scond[$pKey]->value : null;
      $this->setCondition($idValue);
    } else {
      $this->setCondition($param1, $param2, $param3);
    }

    if (empty($this->conditions)) {
      $msg  = "Error:remove() all delete? must be set condition.";
      $smpl = "DELETE FROM 'TABLE_NAME'";
      throw new Exception($msg . " or execute executeQuery({$smpl}).");
    }

    $this->doDelete();
  }

  protected function doDelete()
  {
    try {
      $driver = $this->getDriver();
      $driver->delete($this->getTableName(), $this->conditions);
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $driver);
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
    Sabel::using('Schema_CascadeChain');
    if (!class_exists('Schema_CascadeChain', false))
      throw new Exception('Error: class Schema_CascadeChain does not exist.');

    if ($id === null && !$this->isSelected())
      throw new Exception('Error: give the value of id or select the model beforehand.');

    $chain   = Schema_CascadeChain::get();
    $tblName = $this->tableProp->table;

    if (isset($chain[$tblName])) {
      $tables = $chain[$tblName];
    } else {
      throw new Exception("Error: cascadeDelete() '{$tblName}' does not exist in the cascade chain.");
    }

    $this->begin();

    $models = array();
    $pKey   = $this->tableProp->primaryKey;
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

  /**
   * execute a query directly.
   *
   * @param  string $sql   execute query.
   * @param  array  $param character strings where it should escape.
   * @return array
   */
  public function executeQuery($sql, $param = null)
  {
    if (isset($param) && !is_array($param))
      throw new Exception('Error: execute() second argument must be an array');

    try {
      $driver = $this->getDriver();
      $driver->execute($sql, $param);
      return $this->toObject($driver->getResultSet());
    } catch (Exception $e) {
      $this->executeError($e->getMessage(), $driver);
    }
  }

  protected function executeError($errorMsg, $driver)
  {
    $driver->rollback();
    throw new Exception($errorMsg);
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

  protected function getSchemaFromCls($clsName, $tblName)
  {
    $cols = array();
    $sCls = new $clsName();
    foreach ($sCls->get() as $colName => $colInfo) {
      $colInfo['name'] = $colName;
      $cols[$colName]  = new Sabel_ValueObject($colInfo);
    }

    $tblSchema  = Sabel::load('Sabel_DB_Schema_Table', $tblName, $cols);
    $properties = $sCls->getProperty();

    return array($tblSchema, $properties);
  }

  protected function getSchemaFromDb($tblName)
  {
    $conName    = $this->connectName;
    $scmName    = Sabel_DB_Connection::getSchema($conName);
    $database   = Sabel_DB_Connection::getDB($conName);
    $accessor   = Sabel::load('Sabel_DB_Schema_Accessor', $conName, $scmName);
    $engine     = ($database === 'mysql') ? $accessor->getTableEngine($tblName) : null;
    $tblSchema  = $accessor->getTable($tblName);

    $properties = array('primaryKey'   => $tblSchema->getPrimaryKey(),
                        'incrementKey' => $tblSchema->getIncrementKey(),
                        'tableEngine'  => $engine);

    return array($tblSchema, $properties);
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

  /**
   * an alias for setChildConstraint.
   *
   */
  public function cconst($mdlName, $constraints)
  {
    $this->setChildConstraint($mdlName, $constraints);
  }

  /**
   * an alias for setChildCondition.
   *
   */
  public function ccond($arg1, $arg2 = null)
  {
    $this->setChildCondition($arg1, $arg2);
  }
}
