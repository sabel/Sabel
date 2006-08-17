<?php

//uses('sabel.edo.DBConnection');
//uses('sabel.edo.RecordClasses');

//uses('sabel.edo.driver.Pdo');
//uses('sabel.edo.driver.Mysql');
//uses('sabel.edo.driver.Pgsql');

abstract class Sabel_Edo_RecordObject
{
  const WITH_PARENT  = 1;

  protected
    $conditions      = array(),
    $selectCondition = array();

  protected
    $constraints         = array(),
    $childConstraints    = array(),
    $defChildConstraints = array();

  protected
    $edo           = null,
    $connectName   = '',
    $cachedParent  = array(),
    $table         = '',
    $structure     = 'normal',
    $projection    = '*',
    $defColumn     = 'id';

  protected
    $data         = array(),
    $newData      = array(),
    $parentTables = array(),
    $joinColCache = array(),
    $selected     = false,
    $withParent   = false;

  protected function getMyEDO()
  {
    return $this->makeEdoDriver();
  }

  public function setEDO($connectName)
  {
    $this->connectName = $connectName;
    $this->edo = $this->makeEdoDriver();
  }

  protected function makeEdoDriver()
  {
    $conn = Sabel_Edo_DBConnection::getConnection($this->connectName);

    switch (Sabel_Edo_DBConnection::getDriver($this->connectName)) {
      case 'pdo':
        $pdoDb = Sabel_Edo_DBConnection::getDB($this->connectName);
        return new Sabel_Edo_Driver_Pdo($conn, $pdoDb);
      case 'pgsql':
        return new Sabel_Edo_Driver_Pgsql($conn);
    }
  }

  public function __construct($param1 = null, $param2 = null)
  {
    if ($this->table === '')
      $this->table = strtolower(get_class($this));

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

  public function setWithParent($bool)
  {
    $this->withParent = $bool;
  }

  public function setProperties($array)
  {
    foreach ($array as $key => $val) $this->$key = $val;
  }

  public function getStructure()
  {
    return $this->structure;
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
    if (isset($param2)) {
      if (is_array($param2)) {
        $this->childConstraints[$param1] = $param2;
      } else {
        $this->defChildConstraints = array($param1 => $param2);
      }
    } else {
      if (is_array($param1)) {
        $this->defChildConstraints = $param1;
      } else {
        throw new Exception('Error: setChildConstraint() when Argument 2 is null, Argument 1 must be an Array');
      }
    }
  }

  protected function receiveChildConstraint($constraints)
  {
    if (!is_array($constraints)) throw new Exception('constrains is not array.');

    $this->childConstraints = $constraints;
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
    $this->edo->begin();
  }

  public function commit()
  {
    $this->edo->commit();
  }

  public function getCount($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setCondition($param1, $param2, $param3);

    $edo = $this->edo;
    $edo->setBasicSQL("SELECT count(*) FROM {$this->table}");
    $edo->makeQuery($this->conditions, array('limit' => 1));

    if ($edo->execute()) {
      $row = $edo->fetch();
      return (int) $row[0];
    } else {
      throw new Exception('Error: getCount() execute failed.');
    }
  }

  public function getFirst($orderColumn)
  {
    return $this->getMost('asc', $orderColumn);
  }

  public function getLast($orderColumn)
  {
    return $this->getMost('desc', $orderColumn);
  }

  protected function getMost($type, $orderColumn)
  {
    $this->setCondition($orderColumn, 'not null');
    $this->setConstraint(array('limit' => 1, 'order' => "{$orderColumn} {$type}"));
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

    $edo = $this->edo;
    $edo->setAggregateSQL($table, $idColumn, $functions);
    $edo->makeQuery(null, $this->constraints);

    if ($edo->execute($sql)) {
      $rows = $edo->fetchAll(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      return $this->toObject($rows);
    } else {
      return false;
    }
  }

  protected function defaultSelectOne($param1, $param2 = null)
  {
    $this->setCondition($param1, $param2);
    $this->selectCondition = $this->conditions;

    $this->makeFindObject($this);
  }

  public function selectOne($param1 = null, $param2 = null, $param3 = null)
  {
    if (is_null($param1) && is_null($this->conditions))
      throw new Exception('Error: selectOne() [WHERE] must be set condition.');

    $this->setSelectCondition($param1, $param2, $param3);
    $this->selectCondition = $this->conditions;

    return $this->makeFindObject(clone($this));
  }

  protected function makeFindObject($obj)
  {
    $edo = $this->edo;
    $edo->setBasicSQL("SELECT {$obj->projection} FROM {$this->table}");
    $edo->makeQuery($this->conditions, $this->constraints);

    if ($edo->execute()) {
      if ($row = $edo->fetch(Sabel_Edo_Driver_Interface::FETCH_ASSOC)) {
        if ($this->withParent) {
          $row = $this->selectWithParent($row);
        }
        $this->setSelectedProperty($obj, $row[$obj->defColumn], $row);

        $myChild = $this->getMyChildren();
        if (isset($myChild)) $this->getDefaultChild($myChild, $obj);
      } else {
        $obj->data = $this->conditions;
      }
      $this->constraints = array();
      $this->conditions  = array();
      return $obj;
    } else {
      throw new Exception('Error: makeFindObject() execute failed.');
    }
  }

  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setSelectCondition($param1, $param2, $param3);
    $this->edo->setBasicSQL("SELECT {$this->projection} FROM {$this->table}");
    return $this->getRecords($this->conditions, $this->constraints);
  }

  private function setSelectCondition($param1, $param2, $param3)
  {
    if ($param1 === self::WITH_PARENT) {
      $this->setWithParent(true);
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
    $is = new Edo_InformationSchema($this->connectName, $schema);
    $this->addJoinColumnPhrase($is, $sql, $table);

    if ($child)  $this->addJoinColumnPhrase($is, $sql, $child);
    if ($parent) $this->addJoinColumnPhrase($is, $sql, $parent);

    $sql = join('', $sql);
    $sql = array(substr($sql, 0, strlen($sql) - 2));
    array_push($sql, " FROM {$table}");

    if ($child)  array_push($sql, $this->getLeftJoinPhrase($child,  $table, 'child'));
    if ($parent) array_push($sql, $this->getLeftJoinPhrase($parent, $table, 'parent'));

    $edo = $this->edo;
    $edo->setBasicSQL(join('', $sql));
    $edo->makeQuery($this->condition, $this->constraints);
    if ($edo->execute()) {
      $rows = $edo->fetchAll(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      $relTables = array_merge($child, $parent);

      $recordObj = array();
      foreach ($rows as $row) {
        foreach ($relTables as $table) {
          foreach ($this->joinColCache[$table] as $column) {
            $row[$table][$column] = $row["prefix_{$table}_{$column}"];
            unset($row["prefix_{$table}_{$column}"]);
          }
          $obj = $this->newClass($table);
          $this->setSelectedProperty($obj, $row[$table][$obj->defColumn], $row[$table]);
          $obj->setTable($table);
          $row[$table] = $obj;
        }
        $obj = $this->newClass($this->table);
        $this->setSelectedProperty($obj, $row[$obj->defColumn], $row);
        $recordObj[] = $obj;
      }
      return $recordObj;
    } else {
      throw new Exception('Error: selectJoin() failed');
    }
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
      if ($type === 'child') {
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

    $condition = array("{$obj->table}_id" => $obj->data[$obj->defColumn]);

    $obj->edo->setBasicSQL("SELECT {$obj->projection} FROM {$child}");
    $obj->data[$child] = $obj->getRecords($condition, $obj->childConstraints[$child], $child);
  }

  protected function getRecords($conditions, $constraints = null, $child_table = null)
  {
    $this->edo->makeQuery($conditions, $constraints);

    if ($this->edo->execute()) {
      $rows = $this->edo->fetchAll(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      if (!$rows) return null;

      $recordObj = array();

      foreach ($rows as $row) {
        if (is_null($child_table)) {
          $obj = $this->newClass($this->table);
          $obj->receiveChildConstraint($this->childConstraints);
        } else {
          $obj = $this->newClass($child_table);
        }

        if ($this->withParent) $row = $this->selectWithParent($row);

        $this->setSelectedProperty($obj, $row[$obj->defColumn], $row);

        $myChild = $obj->getMyChildren();
        if (isset($myChild)) {
          if (isset($child_table)) $this->chooseMyChildConstraint($myChild, $obj);
          $this->getDefaultChild($myChild, $obj);
        }
        $recordObj[] = $obj;
      }
      $this->constraints = array();
      $this->conditions  = array();
      return $recordObj;
    } else {
      throw new Exception('Error: getRecords() execute failed.');
    }
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
      $constraints = $this->constraintMerge($child, $this->childConstraints[$child]);
    } else if (!empty($this->defChildConstraints)) {
      $constraints = $this->defChildConstraints;
    } else if (!($constraints = $this->hasMyChildConstraint($child, $obj))) {
      $constraints = $this->hasDefaultChildConstraint($obj);
    }

    $obj->setChildConstraint($child, $constraints);
    $obj->defChildConstraints = $this->defChildConstraints;
  }

  private function constraintMerge($child, $constraints)
  {
    if ($results = $this->hasMyChildConstraint($child, $this)) {
      foreach ($results as $key => $val) {
        if (!array_key_exists($key, $constraints)) $constraints[$key] = $val;
      }
    }
    return $constraints;
  }

  private function hasMyChildConstraint($child, $obj)
  {
    $childConstraints = $obj->getMyChildConstraint();
    if (!is_array($childConstraints)) return false;

    if (array_key_exists($child, $childConstraints)) {
      return $childConstraints[$child];
    } else {
      return false;
    }
  }

  private function hasDefaultChildConstraint($obj)
  {
    if (!empty($obj->defChildConstraints)) {
      return $obj->defChildConstraints;
    } else {
      throw new Exception('Error: constraint of child object, not found.');
    }
  }

  protected function selectWithParent($row)
  {
    foreach ($row as $key => $val) {
      if (strpos($key, '_id')) {
        $table = str_replace('_id', '', $key);

        $this->parentTables = array($this->table);
        $row[$table] = $this->addParentObject($table, $val);
      }
    }
    $this->parentTables = array($this->table);
    return $row;
  }

  protected function addParentObject($table, $id)
  {
    if ($this->getStructure() !== 'tree' && $this->isAcquiredObject($table)) return null;

    $obj = $this->newClass($table);
    if (is_null($id)) return $obj;

    if (!is_array($row = Sabel_Edo_SimpleCache::get($table . $id))) {
      $edo = $this->getMyEDO();
      $edo->setBasicSQL("SELECT {$obj->projection} FROM {$table}");
      $edo->makeQuery(array($obj->defColumn => $id));

      if ($edo->execute()) {
        $row = $edo->fetch(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
        if (empty($row)) return $obj;
        Sabel_Edo_SimpleCache::add($table . $id, $row);
      } else {
        throw new Exception('Error: addParentObject() execute failed.');
      }
    }

    foreach ($row as $key => $val) {
      if (strpos($key, '_id')) {
        $key = str_replace('_id', '', $key);
        $row[$key] = $this->addParentObject($key, $val);
      } else {
        $row[$key] = $val;
      }
    }
    $this->setSelectedProperty($obj, $id, $row);
    $obj->newData = array();
    return $obj;
  }

  private function setSelectedProperty($obj, $id, $row)
  {
    $obj->selectCondition[$obj->defColumn] = $id;
    $obj->setProperties($row);
    $obj->selected = true;
  }

  private function isAcquiredObject($table)
  {
    $pt = $this->parentTables;
    if (in_array($table, $pt)) return true;

    $pt[] = $table;
    $this->parentTables = $pt;
    return false;
  }

  public function newChild($child = null)
  {
    $id = $this->data[$this->defColumn];
    if (empty($id))
      throw new Exception('Error: who is a parent? hasn\'t id value.');

    $parent = strtolower(get_class($this));
    $table  = (is_null($child)) ? $parant : $child;
    $obj    = $this->newClass($table);

    $column = $parent . '_id';
    $obj->$column = $id;
    return $obj;
  }

  protected function newClass($name)
  {
    if (class_exists($name, false) && strtolower($name) !== 'sabel_edo_commonrecord') {
      return new $name();
    } else {
      return new Sabel_Edo_CommonRecord($this->table);
    }
  }

  public function clearChild($child)
  {
    $id = $this->data[$this->defColumn];
    if (empty($id))
      throw new Exception('Error: who is a parent? hasn\'t id value.');

    $parent = strtolower(get_class($this));
    $this->table = $child;
    $this->remove("{$parent}_id", $id);

    $this->table = $parant; 
  }

  public function save($data = null)
  {
    if (!empty($data)) $this->data = $data;
    return ($this->is_selected()) ? $this->update() : $this->insert();
  }

  public function allUpdate($data)
  {
    $this->edo->setUpdateSQL($this->table, $data);
    $this->edo->makeQuery($this->conditions);

    if ($this->edo->execute()) {
      $this->conditions = array();
    } else {
      throw new Exception('Error: allUpdate() execute failed.');
    }
  }

  protected function update()
  {
    $this->edo->setUpdateSQL($this->table, $this->newData);
    $this->edo->makeQuery($this->selectCondition);

    if ($this->edo->execute()) {
      $this->selectCondition = array();
    } else {
      throw new Exception('Error: update() execute failed.');
    }
  }

  protected function insert()
  {
    if ($this->edo->executeInsert($this->table, $this->data, $this->defColumn)) {
      return $this->edo->getLastInsertId();
    } else {
      throw new Exception('Error: insert() execute failed.');
    }
  }

  public function multipleInsert($data)
  {
    if (!is_array($data))
      throw new Exception('Error: data is not array.');

    foreach ($data as $val) {
      if (!$this->edo->executeInsert($this->table, $val, $this->defColumn)) {
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

    $this->edo->setBasicSQL("DELETE FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    if ($this->edo->execute()) {
      $this->conditions  = array();
      $this->constraints = array();
    } else {
      throw new Exception('Error: remove() execute failed.');
    }
  }

  public function execute($sql)
  {
    if ($this->edo->execute($sql)) {
      $rows = $this->edo->fetchAll(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      return $this->toObject($rows);
    } else {
      return false;
    }
  }

  protected function toObject($array)
  {
    if (empty($array)) return null;

    $recordObj = array();
    foreach ($array as $row) {
      $obj = $this->newClass($this->table);
      $obj->setProperties($row);
      $recordObj[] = $obj;
    }
    return $recordObj;
  }
}

class Sabel_Edo_SimpleCache
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

class Sabel_Edo_CommonRecord extends Sabel_Edo_RecordObject
{
  public function __construct($table = null)
  {
    $this->setEDO('user');
    parent::__construct();

    if (isset($table)) $this->table = $table;
  }
}

abstract class BaseBridgeRecord extends Sabel_Edo_RecordObject
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->structure = 'tree';
    $this->setEDO('user');
    parent::__construct($param1, $param2);
  }

  public function getChild($child, $bridge = null)
  {
    if (is_null($bridge))
      throw new Exception('BaseBridgeRecord::getChild() need a name of a bridge table.');

    $this->setWithParent(true);
    parent::getChild($bridge);

    $children = array();
    foreach ($this->$bridge as $bridge) {
      $children[] = $bridge->$child;
    }
    $this->$child = $children;
  }
}

abstract class BaseTreeRecord extends Sabel_Edo_RecordObject
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->structure = 'tree';
    $this->setEDO('user');
    parent::__construct($param1, $param2);
  }

  protected function addLeaf()
  {
    $obj->leaf = true;
    //@todo
  }

  protected function getLeaf()
  {
    $this->leaf(true);
    //@todo
  }

  protected function getRoot()
  {
    return $this->select("{$this->table}_id", 'null');
  }
}
