
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

  protected function getDriver()
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
    return $this->data[$key];
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
    if (!is_array($array)) throw new Exception('properties argument is not array.');
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
    $this->setCondition($method, $parameters[0], $parameters[1]);
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
    $this->driver->begin();
  }

  public function commit()
  {
    $this->driver->commit();
  }

  public function getCount($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setCondition($param1, $param2, $param3);

    $driver = $this->getDriver();
    $driver->setBasicSQL("SELECT count(*) FROM {$this->table}");
    $driver->makeQuery($this->conditions, array('limit' => 1));

    if (!$driver->execute()) throw new Exception('Error: getCount() execute failed.');
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

    if ($driver->execute($sql)) {
      $rows = $driver->fetchAll(Sabel_DB_Driver_Interface::FETCH_ASSOC);
      return $this->toObject($rows);
    } else {
      return false;
    }
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

    $this->setSelectCondition($param1, $param2, $param3);
    return $this->makeFindObject(clone($this));
  }

  protected function makeFindObject($obj)
  {
    $driver = $this->getDriver();
    $driver->setBasicSQL("SELECT {$obj->projection} FROM {$this->table}");
    $driver->makeQuery($this->conditions, $this->constraints);

    $this->selectCondition = $this->conditions;

    if (!$driver->execute()) throw new Exception('Error: makeFindObject() execute failed.');
    if ($row = $driver->fetch(Sabel_DB_Driver_Interface::FETCH_ASSOC)) {
      if ($this->withParent) $row = $this->selectWithParent($row);

      $this->setSelectedProperty($obj, $row);

      if (!is_null($myChild = $this->getMyChildren()))
        $this->getDefaultChild($myChild, $obj);
    } else {
      $obj->data = $this->conditions;
    }

    $this->constraints = array();
    $this->conditions  = array();
    return $obj;
  }

  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setSelectCondition($param1, $param2, $param3);
    $this->driver->setBasicSQL("SELECT {$this->projection} FROM {$this->table}");
    return $this->getRecords($this->conditions, $this->constraints);
  }

  private function setSelectCondition($param1, $param2, $param3)
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
    $is = new Sabel_DB_Schema($this->connectName, $schema);
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
    if (!$driver->execute()) throw new Exception('Error: selectJoin() failed');

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

  public function getChild($child, $obj = null)
  {
    if (is_null($obj)) $obj = $this;

    $obj->chooseMyChildConstraint($child, $obj);

    if (is_null($obj->childConstraints[$child]['limit']))
      throw new Exception('Error: getChildren() must be set limit constraints');

    $obj->childConditions[$child]["{$obj->table}_id"] = $obj->data[$obj->defColumn];

    $obj->driver->setBasicSQL("SELECT {$obj->projection} FROM {$child}");
    $obj->data[$child] = $obj->getRecords($obj->childConditions[$child], $obj->childConstraints[$child], $child);
  }

  protected function getRecords($conditions, $constraints = null, $child = null)
  {
    $driver = $this->getDriver();
    $driver->makeQuery($conditions, $constraints);

    if (!$driver->execute()) throw new Exception('Error: getRecords() execute failed.');

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
      $driver = $this->getDriver();
      $driver->setBasicSQL("SELECT {$obj->projection} FROM {$table}");
      $driver->makeQuery(array($obj->defColumn => $id));

      if (!$driver->execute()) throw new Exception('Error: addParentObject() execute failed.');
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
    $obj->selectCondition[$obj->defColumn] = $row[$obj->defColumn];
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
    if (class_exists($name, false) && strtolower($name) !== 'sabel_db_commonrecord') {
      return new $name();
    } else {
      return new Sabel_DB_Basic($name);
    }
  }

  public function clearChild($child)
  {
    $id = $this->data[$this->defColumn];
    if (empty($id)) throw new Exception('Error: who is a parent? hasn\'t id value.');

    $parent = strtolower(get_class($this));
    $this->table = $child;
    $this->remove("{$parent}_id", $id);

    $this->table = $parant;
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

    if ($driver->execute()) {
      $this->selectCondition = array();
    } else {
      throw new Exception('Error: update() execute failed.');
    }
  }

  protected function insert()
  {
    if ($this->driver->executeInsert($this->table, $this->data, $this->defColumn)) {
      return $this->driver->getLastInsertId();
    } else {
      throw new Exception('Error: insert() execute failed.');
    }
  }

  public function multipleInsert($data)
  {
    if (!is_array($data)) throw new Exception('Error: data is not array.');

    foreach ($data as $val) {
      if (!$this->driver->executeInsert($this->table, $val, $this->defColumn)) {
        throw new Exception('Error: multipleInsert() execute failed.');
      }
    }
  }

  public function remove($param1 = null, $param2 = null, $param3 = null)
  {
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

    if ($driver->execute()) {
      $this->conditions  = array();
      $this->constraints = array();
    } else {
      throw new Exception('Error: remove() execute failed.');
    }
  }

  public function execute($sql)
  {
    if ($this->driver->execute($sql)) {
      $rows = $this->driver->fetchAll(Sabel_DB_Driver_Interface::FETCH_ASSOC);
      return $this->toObject($rows);
    } else {
      return false;
    }
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
    return self::$cache[$key];
  }
}

class Sabel_DB_Basic extends Sabel_DB_Mapper
{
  public function __construct($table = null)
  {
    $this->setDriver('user');
    parent::__construct();

    if (isset($table)) $this->table = $table;
  }
}

abstract class Sabel_DB_Bridge extends Sabel_DB_Mapper
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->structure = 'bridge';
    $this->setDriver('user');
    parent::__construct($param1, $param2);
  }

  public function getChild($child, $obj = null)
  {
    $this->enableParent();
    parent::getChild($obj);

    $children = array();
    foreach ($this->$obj as $bridge) $children[] = $bridge->$child;
    $this->$child = $children;
  }
}

abstract class Sabel_DB_Tree extends Sabel_DB_Mapper
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->structure = 'tree';
    $this->setDriver('user');
    parent::__construct($param1, $param2);
  }

  protected function addLeaf()
  {
    $obj->leaf = true;
    // @todo ???
  }

  protected function getLeaf()
  {
    $this->leaf(true);
    // @todo ???
  }

  public function getRoot()
  {
    return $this->select("{$this->table}_id", 'null');
  }
}