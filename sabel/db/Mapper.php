<?php

/**
 * important class of sabel_db package.
 * inherit this class to use a orm solution.
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
abstract class Sabel_DB_Mapper
{
  const WITH_PARENT = 1;

  const TYPE_CHILD  = 0;
  const TYPE_PARENT = 1;

  protected
    $conditions      = array(),
    $selectCondition = array();

  protected
    $constraints         = array(),
    $childConditions     = array(),
    $childConstraints    = array(),
    $defChildConstraints = array(),
    $myChildren          = null;

  protected
    $driver       = null,
    $connectName  = '',
    $cachedParent = array(),
    $table        = '',
    $structure    = 'normal',
    $projection   = '*',
    $defColumn    = 'id';

  protected
    $data         = array(),
    $newData      = array(),
    $parentTables = array(),
    $joinColCache = array(),
    $selected     = false,
    $withParent   = false;

  public function getDriver()
  {
    return $this->driver;
  }

  public function setDriver($connectName)
  {
    $this->driver = $this->makeEdoDriver($connectName);
  }

  protected function makeEdoDriver($connectName)
  {
    $this->connectName = $connectName;
    $conn = Sabel_DB_Connection::getConnection($connectName);

    switch (Sabel_DB_Connection::getDriver($connectName)) {
      case 'pdo':
        $pdoDb = Sabel_DB_Connection::getDB($connectName);
        return new Sabel_DB_Driver_Pdo($conn, $pdoDb);
      case 'pgsql':
        return new Sabel_DB_Driver_Pgsql($conn);
    }
  }

  public function __construct($param1 = null, $param2 = null)
  {
    if (Sabel_DB_Transaction::isActive()) $this->begin();

    if ($this->table === '') $this->table = strtolower(get_class($this));
    if (isset($param1)) $this->defaultSelectOne($param1, $param2);
  }

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
    if ($this->is_selected()) $this->newData[$key] = $val;
  }

  public function __get($key)
  {
    return (isset($this->data[$key])) ? $this->data[$key] : null;
  }

  public function is_selected()
  {
    return $this->selected;
  }

  public function toArray()
  {
    return $this->data;
  }

  public function setProjection($p)
  {
    $this->projection = (is_array($p)) ? implode(',', $p) : $p;
  }

  public function setDefaultColumn($column)
  {
    $this->defColumn = $column;
  }

  public function setTable($table)
  {
    $this->table = $table;
  }

  public function enableParent()
  {
    $this->withParent = true;
  }

  public function setProperties($array)
  {
    if (!is_array($array)) throw new Exception('properties Argument is not array.');
    foreach ($array as $key => $val) $this->$key = $val;
  }

  public function getStructure()
  {
    return $this->structure;
  }

  public function getMyChildren()
  {
    return $this->myChildren;
  }

  public function getMyChildConstraint()
  {
    return $this->childConstraints;
  }

  public function __call($method, $parameters)
  {
    @list($paramOne, $paramTwo) = $parameters;
    $this->setCondition($method, $paramOne, $paramTwo);
  }

  public function setConstraint($param1, $param2 = null)
  {
    if (!is_array($param1)) $param1 = array($param1 => $param2);

    foreach ($param1 as $key => $val) {
      if (isset($val)) {
        $this->constraints[$key] = $val;
      } else {
        throw new Exception('Error: setConstraint() constraint value is null.');
      }
    }
  }

  public function setChildConstraint($param1, $param2 = null)
  {
    if (isset($param2) && is_array($param2)) {
      foreach ($param2 as $key => $val)
        $this->childConstraints[$param1][$key] = $val;
    } else if (isset($param2)) {
      $this->defChildConstraints = array($param1 => $param2);
    } else if (is_array($param1)) {
      $this->defChildConstraints = $param1;
    } else {
      throw new Exception('Error: setChildConstraint() when Argument 2 is null, Argument 1 must be an Array');
    }
  }

  public function setChildCondition($child, $conditions)
  {
    if (!is_array($conditions))
      throw new Exception('Error: setChildCondition() Argument 2 must be an Array');

    $this->childConditions[$child] = $conditions;
  }

  protected function receiveChildConstraint($constraints)
  {
    if (!is_array($constraints)) throw new Exception('constrains is not array.');
    if ($constraints) $this->childConstraints = $constraints;
  }

  /**
   * setting condition
   *
   * @param mixed string or int this value use tow means for
   *          default column value or a condition column name.
   * @param mixed string or int or NULL
   *          this value use three means for
   *          default column value or when has param3 value of special condition
   *          or when has no param3 param2 is value
   * @param mixed string or int or NULL
   *          this value use for value of special condition.
   * @return void
   */
  public function setCondition($param1, $param2 = null, $param3 = null)
  {
    if (empty($param1)) return null;

    if ($this->isSpecialParam($param3, $param1)) {
      if (is_null($param2)) throw new Exception('Error: setCondition() Argument 2 is null.');
      $this->conditions[$param1] = array($param2, $param3);
    } else if ($this->isDefaultColumnValue($param2)) {
      $this->conditions[$this->defColumn] = $param1;
    } else {
      $this->conditions[$param1] = $param2;
    }
  }

  private function isSpecialParam($param3, $param1)
  {
    return (isset($param3) && !is_array($param1));
  }

  private function isDefaultColumnValue($param2)
  {
    return is_null($param2);
  }

  public function begin()
  {
    $driver = $this->getDriver();

    Sabel_DB_Transaction::begin($this->connectName, $driver->getConnection(), $driver);
    Sabel_DB_Transaction::enableTransaction();
  }

  public function commit()
  {
    Sabel_DB_Transaction::commit();
  }

  public function getCount($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setCondition($param1, $param2, $param3);

    $driver = $this->getDriver();
    $driver->setBasicSQL("SELECT count(*) FROM {$this->table}");
    $driver->makeQuery($this->conditions, array('limit' => 1));

    $this->tryExecute($driver);
    $row = $driver->fetch();
    return (int) $row[0];
  }

  public function getFirst($orderColumn)
  {
    return $this->getMost('ASC', $orderColumn);
  }

  public function getLast($orderColumn)
  {
    return $this->getMost('DESC', $orderColumn);
  }

  protected function getMost($order, $orderColumn)
  {
    $this->setCondition($orderColumn, 'NOT NULL');
    $this->setConstraint(array('limit' => 1, 'order' => "{$orderColumn} {$order}"));
    return $this->selectOne();
  }

  public function aggregate($functions, $child = null)
  {
    if (is_null($child)) {
      $table    = $this->table;
      $idColumn = $this->defColumn;
    } else {
      $table    = $child;
      $idColumn = $this->table . '_id';
    }

    $driver = $this->getDriver();
    $driver->setAggregateSQL($table, $idColumn, $functions);
    $driver->makeQuery(null, $this->constraints);

    $this->tryExecute($driver);
    $rows = $driver->fetchAll(Sabel_DB_Driver_Interface::FETCH_ASSOC);
    return $this->toObject($rows);
  }

  protected function defaultSelectOne($param1, $param2 = null)
  {
    $this->setCondition($param1, $param2);
    $this->makeFindObject($this);
  }

  public function selectOne($param1 = null, $param2 = null, $param3 = null)
  {
    if (is_null($param1) && is_null($this->conditions))
      throw new Exception('Error: selectOne() [WHERE] must be set condition.');

    $this->addSelectCondition($param1, $param2, $param3);
    return $this->makeFindObject(clone($this));
  }

  protected function makeFindObject($obj)
  {
    $driver = $obj->getDriver();
    $driver->setBasicSQL("SELECT {$obj->projection} FROM {$obj->table}");
    $driver->makeQuery($obj->conditions, $obj->constraints);

    $obj->selectCondition = $obj->conditions;

    $this->tryExecute($driver);
    if ($row = $driver->fetch(Sabel_DB_Driver_Interface::FETCH_ASSOC)) {
      if ($obj->withParent) $row = $obj->selectWithParent($row);

      $obj->setSelectedProperty($obj, $row);

      if (!is_null($myChild = $obj->getMyChildren()))
        $obj->getDefaultChild($myChild, $obj);
    } else {
      $obj->data = $obj->conditions;
    }

    $this->constraints = array();
    $this->conditions  = array();
    return $obj;
  }

  private function addSelectCondition($param1, $param2, $param3)
  {
    if ($param1 === self::WITH_PARENT) {
      $this->enableParent();
    } else {
      $this->setCondition($param1, $param2, $param3);
    }
  }

  public function selectJoin($relTableList)
  {
    $child  = $relTableList['child'];
    $parent = $relTableList['parent'];

    if (!$child && !$parent)
      throw new Exception('Error: joinSelect() invalid parameter.');

    if (isset($child)  && !is_array($child))  $child  = array($child);
    if (isset($parent) && !is_array($parent)) $parent = array($parent);

    $sql   = array('SELECT ');
    $table = $this->table;

    $schema = 'edo'; //tmp
    $is = new Sabel_DB_Schema_Accessor($this->connectName, $schema);
    $this->addJoinColumnPhrase($is, $sql, $table);

    if ($child)  $this->addJoinColumnPhrase($is, $sql, $child);
    if ($parent) $this->addJoinColumnPhrase($is, $sql, $parent);

    $sql = join('', $sql);
    $sql = array(substr($sql, 0, strlen($sql) - 2));
    array_push($sql, " FROM {$table}");

    if ($child)  array_push($sql, $this->getLeftJoinPhrase($child,  $table, self::TYPE_CHILD));
    if ($parent) array_push($sql, $this->getLeftJoinPhrase($parent, $table, self::TYPE_PARENT));

    $driver = $this->getDriver();
    $driver->setBasicSQL(join('', $sql));
    $driver->makeQuery($this->condition, $this->constraints);

    $this->tryExecute($driver);
    $rows = $driver->fetchAll(Sabel_DB_Driver_Interface::FETCH_ASSOC);
    $relTables = array_merge($child, $parent);

    $recordObj = array();
    foreach ($rows as $row) {
      foreach ($relTables as $table) {
        foreach ($this->joinColCache[$table] as $column) {
          $row[$table][$column] = $row["prefix_{$table}_{$column}"];
          unset($row["prefix_{$table}_{$column}"]);
        }
        $obj = $this->newClass($table);
        $this->setSelectedProperty($obj, $row[$table]);
        $obj->setTable($table);
        $row[$table] = $obj;
      }
      $obj = $this->newClass($this->table);
      $this->setSelectedProperty($obj, $row);
      $recordObj[] = $obj;
    }
    return $recordObj;
  }

  private function addJoinColumnPhrase($is, &$sql, $table)
  {
    if (is_array($table)) {
      foreach ($table as $t) {
        $joinCol = array();
        foreach ($is->getTable($t)->getColumns() as $c) {
          $joinCol[] = $c->name;
          array_push($sql, "{$t}.{$c->name} AS prefix_{$t}_{$c->name}, ");
        }
        $this->joinColCache[$t] = $joinCol;
      }
    } else {
      foreach ($is->getTable($table)->getColumns() as $c) {
        array_push($sql, "{$table}.{$c->name}, ");
      }
    }
  }

  private function getLeftJoinPhrase($rel, $table, $type)
  {
    foreach ($rel as $val) {
      if ($type === self::TYPE_CHILD) {
        return " LEFT JOIN {$val} ON {$table}.id = {$val}.{$table}_id";
      } else {
        return " LEFT JOIN {$val} ON {$table}.{$val}_id = {$val}.id ";
      }
    }
  }

  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    $driver = $this->getDriver();
    $this->addSelectCondition($param1, $param2, $param3);
    $driver->setBasicSQL("SELECT {$this->projection} FROM {$this->table}");
    return $this->getRecords($driver, $this->conditions, $this->constraints);
  }

  public function getChild($child, $obj = null)
  {
    if (is_null($obj)) $obj = $this;

    $this->chooseMyChildConstraint($child, $obj);

    if (is_null($obj->childConstraints[$child]['limit']))
      throw new Exception('Error: getChildren() must be set limit constraints');

    $obj->childConditions[$child]["{$obj->table}_id"] = $obj->data[$obj->defColumn];

    $driver = $this->newClass($child)->getDriver();
    $driver->setBasicSQL("SELECT {$obj->projection} FROM {$child}");
    $conditions  = $obj->childConditions[$child];
    $constraints = $obj->childConstraints[$child];

    if ($children = $obj->getRecords($driver, $conditions, $constraints, $child)) {
      $obj->data[$child] = $children;
      return $children;
    } else {
      return false;
    }
  }

  protected function getRecords($driver, $conditions, $constraints, $child = null)
  {
    $driver->makeQuery($conditions, $constraints);
    $this->tryExecute($driver);

    $rows = $driver->fetchAll(Sabel_DB_Driver_Interface::FETCH_ASSOC);
    if (!$rows) return false;

    $recordObj = array();
    foreach ($rows as $row) {
      if (is_null($child)) {
        $obj = $this->newClass($this->table);
        $obj->receiveChildConstraint($this->childConstraints);
      } else {
        $obj = $this->newClass($child);
      }

      if ($this->withParent) $row = $this->selectWithParent($row);

      $this->setSelectedProperty($obj, $row);

      if (!is_null($myChild = $obj->getMyChildren())) {
        if (isset($child)) $this->chooseMyChildConstraint($myChild, $obj);
        $this->getDefaultChild($myChild, $obj);
      }
      $recordObj[] = $obj;
    }
    $this->constraints = array();
    $this->conditions  = array();
    return $recordObj;
  }

  protected function getDefaultChild($children, $obj)
  {
    if (!is_array($children)) $children = array($children);

    foreach ($children as $val) {
      $this->chooseMyChildConstraint($val, $obj);
      $obj->getChild($val, $obj);
    }
  }

  private function chooseMyChildConstraint($child, $obj)
  {
    if (array_key_exists($child, $this->childConstraints)) {
      $constraints = $this->childConstraints[$child];
    } else if ($this->defChildConstraints) {
      $constraints = $this->defChildConstraints;
    } else if (!($constraints = $obj->childConstraints[$child])) {
      $constraints = $this->hasDefaultChildConstraint($obj);
    }

    $obj->setChildConstraint($child, $constraints);
    $obj->defChildConstraints = $this->defChildConstraints;
  }

  private function hasDefaultChildConstraint($obj)
  {
    if ($obj->defChildConstraints) {
      return $obj->defChildConstraints;
    } else {
      throw new Exception('Error: constraint of child object, not found.');
    }
  }

  protected function selectWithParent($row)
  {
    $this->parentTables = array($this->table);
    foreach ($row as $key => $val) {
      if (strpos($key, '_id')) {
        $table = str_replace('_id', '', $key);
        $row[$table] = $this->addParentObject($table, $val);
      }
    }
    return $row;
  }

  protected function addParentObject($table, $id)
  {
    if ($this->getStructure() !== 'tree' && $this->isAcquired($table)) return null;

    $obj = $this->newClass($table);
    if (is_null($id)) return $obj;

    if (!is_array($row = Sabel_DB_ParentCache::get($table . $id))) {
      $driver = $obj->getDriver();
      $driver->setBasicSQL("SELECT {$obj->projection} FROM {$table}");
      $driver->makeQuery(array($obj->defColumn => $id));

      $this->tryExecute($driver);
      $row = $driver->fetch(Sabel_DB_Driver_Interface::FETCH_ASSOC);
      if (!$row) {
        $obj->selected = true;
        $obj->id = $id;
        return $obj;
      }
      Sabel_DB_ParentCache::add($table . $id, $row);
    }

    foreach ($row as $key => $val) {
      if (strpos($key, '_id')) {
        $key = str_replace('_id', '', $key);
        $row[$key] = $this->addParentObject($key, $val);
      } else {
        $row[$key] = $val;
      }
    }
    $this->setSelectedProperty($obj, $row);
    $obj->newData = array();
    return $obj;
  }

  private function setSelectedProperty($obj, $row)
  {
    $value = (isset($row[$obj->defColumn])) ? $row[$obj->defColumn] : null;
    $obj->selectCondition[$obj->defColumn] = $value;
    $obj->setProperties($row);
    $obj->selected = true;
  }

  private function isAcquired($table)
  {
    if (in_array($table, $this->parentTables)) return true;
    $this->parentTables[] = $table;
    return false;
  }

  public function newChild($child = null)
  {
    $id = $this->data[$this->defColumn];
    if (empty($id)) throw new Exception('Error: who is a parent? hasn\'t id value.');

    $parent = strtolower(get_class($this));
    $table  = (is_null($child)) ? $parant : $child;
    $obj    = $this->newClass($table);

    $column = $parent . '_id';
    $obj->$column = $id;
    return $obj;
  }

  protected function newClass($name)
  {
    if ($this->mapper_class_exists($name)) {
      return new $name();
    } else {
      return new Sabel_DB_Basic($name);
    }
  }

  private function mapper_class_exists($className)
  {
    return (class_exists($className, false) && strtolower($className) !== 'sabel_db_basic');
  }

  public function clearChild($child)
  {
    $parent = '';

    $id = $this->data[$this->defColumn];
    if (empty($id)) throw new Exception('Error: who is a parent? hasn\'t id value.');

    $parent = strtolower(get_class($this));
    $this->table = $child;
    $this->remove("{$parent}_id", $id);

    $this->table = $parent;
  }

  public function save($data = null)
  {
    if (isset($data) && !is_array($data)) throw new Exception('Error: Argument must be an Array');

    if ($this->is_selected()) {
      if ($data) $this->newData = $data;
      return $this->update();
    } else {
      if ($data) $this->data = $data;
      return $this->insert();
    }
  }

  public function allUpdate($data)
  {
    $driver = $this->getDriver();
    $driver->setUpdateSQL($this->table, $data);
    $driver->makeQuery($this->conditions);

    if ($driver->execute()) {
      $this->conditions = array();
    } else {
      throw new Exception('Error: allUpdate() execute failed.');
    }
  }

  protected function update()
  {
    $driver = $this->getDriver();
    $driver->setUpdateSQL($this->table, $this->newData);
    $driver->makeQuery($this->selectCondition);

    $this->tryExecute($driver);
    $this->selectCondition = array();
  }

  protected function insert()
  {
    try {
      $this->driver->executeInsert($this->table, $this->data, $this->defColumn);
      return $this->driver->getLastInsertId();
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function multipleInsert($data)
  {
    if (!is_array($data)) throw new Exception('Error: data is not array.');

    $this->begin();
    try {
      foreach ($data as $val)
        $this->driver->executeInsert($this->table, $val, $this->defColumn);

      Sabel_DB_Transaction::commit();
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function remove($param1 = null, $param2 = null, $param3 = null)
  {
    $idValue = null;

    if (isset($this->selectCondition[$this->defColumn]))
      $idValue = $this->selectCondition[$this->defColumn];

    if (is_null($param1) && empty($this->conditions) && is_null($idValue))
      throw new Exception("Error: remove() [WHERE] must be set condition");

    if (isset($param1)) {
      $this->setCondition($param1, $param2, $param3);
    } else {
      $this->setCondition($this->defColumn, $idValue);
    }

    $driver = $this->getDriver();
    $driver->setBasicSQL("DELETE FROM {$this->table}");
    $driver->makeQuery($this->conditions, $this->constraints);

    $this->tryExecute($driver);
    $this->conditions  = array();
    $this->constraints = array();
  }

  public function execute($sql)
  {
    $this->tryExecute($this->driver, $sql);
    $rows = $this->driver->fetchAll(Sabel_DB_Driver_Interface::FETCH_ASSOC);
    return $this->toObject($rows);
  }

  protected function toObject($rows)
  {
    if (empty($rows)) return null;

    $recordObj = array();
    $obj = $this->newClass($this->table);
    foreach ($rows as $row) {
      $cloned = clone($obj);
      $cloned->setProperties($row);
      $recordObj[] = $cloned;
    }
    return $recordObj;
  }

  protected function tryExecute($driver, $sql = null)
  {
    try {
      $driver->execute($sql);
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  protected function executeError($errorMsg)
  {
    if (Sabel_DB_Transaction::isActive()) Sabel_DB_Transaction::rollback();
    throw new Exception($errorMsg);
  }
}

class Sabel_DB_ParentCache
{
  private static $cache = array();

  public static function add($key, $val)
  {
    self::$cache[$key] = $val;
  }

  public static function get($key)
  {
    return (isset(self::$cache[$key])) ? self::$cache[$key] : null;
  }
}
