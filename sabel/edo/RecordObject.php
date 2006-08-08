<?php

//uses('sabel.edo.DBConnection');
//uses('sabel.edo.RecordClasses');

//uses('sabel.edo.driver.Pdo');
//uses('sabel.edo.driver.Mysql');
//uses('sabel.edo.driver.Pgsql');

abstract class Sabel_Edo_RecordObject
{
  protected
    $conditions       = array(),
    $selectCondition  = array();

  protected
    $constraints             = array(),
    $childConstraints        = array(),
    $defaultChildConstraints = array();
    
  protected
    $edo,
    $table,
    $projection = '*',
    $defColumn  = 'id';

  protected
    $owner,
    $useEdo;

  protected 
    $data         = array(),
    $newData      = array(),
    $parentTables = array(),
    $selected     = false;

  protected $selectType = self::SELECT_DEFAULT;

  const SELECT_DEFAULT     = 0;
  const WITH_PARENT_VIEW   = 5;
  const WITH_PARENT_OBJECT = 10;

  protected function getMyEDO()
  {
    $conn = Sabel_Edo_DBConnection::getConnection($this->owner, $this->useEdo);
    
    if ($this->useEdo == 'pdo') {
      $pdoDb = Sabel_Edo_DBConnection::getDB($this->owner);
      return new Sabel_Edo_Driver_Pdo($conn, $pdoDb);
    } elseif ($this->useEdo == 'pgsql') {
      return new Sabel_Edo_Driver_Pgsql($conn);
    } elseif ($this->useEdo == 'mysqli') {
      return new Sabel_Edo_Driver_Mysqli($conn);
    } else {
      //todo
    }
  }

  public function setEDO($owner, $useEdo)
  {
    $this->owner  = $owner;
    $this->useEdo = $useEdo;

    $conn = Sabel_Edo_DBConnection::getConnection($owner, $useEdo);

    if ($useEdo== 'pdo') {
      $pdoDb = Sabel_Edo_DBConnection::getDB($owner);
      $this->edo = new Sabel_Edo_Driver_Pdo($conn, $pdoDb);
    } elseif ($useEdo == 'pgsql') {
      $this->edo = new Sabel_Edo_Driver_Pgsql($conn);
    } elseif ($useEdo == 'mysqli') {
      $this->edo = new Sabel_Edo_Driver_Mysqli($conn);
    } else {
      //todo
    }
  }

  public function __construct($param1 = null, $param2 = null)
  {
    $this->table = strtolower(get_class($this));

    if (!is_null($param1))
      $this->defaultSelectOne($param1, $param2);
  }

  public function __set($key, $val)
  {
    $this->data[$key] = $val;

    if ($this->selected) {
      $this->newData[$key] = $val;
    }
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

  public function setProjection($projection)
  {
    if (is_array($projection)) {
      $this->projection = implode(',', $projection);
    } else {
      $this->projection = $projection;
    }
  }

  public function setDefaultColumn($column)
  {
    $this->defColumn = $column;
  }

  public function setTable($table)
  {
    $this->table = $table;
  }

  public function setSelectType($type)
  {
    $this->selectType = $type;
  }
  
  public function setProperties($array)
  {
    foreach ($array as $key => $val) {
      $this->$key = $val;
    }    
  }

  public function __call($method, $parameters)
  {
    if (!empty($parameters[1])) {
      $this->setCondition($method, $parameters[0], $parameters[1]);
    } else {
      $this->setCondition($method, $parameters[0]);
    }
  }
  
  public function setConstraint($param1, $param2 = null)
  {
    if (is_array($param1)) {
      foreach ($param1 as $key => $val) {
        if (is_null($val)) {
          throw new Exception('Error: setConstraint() constraint value is null');
        } else {
          $this->constraints[$key] = $val;
        }
      }
    } else {
      if (is_null($param2)) {
        throw new Exception('Error: setConstraint() constraint value is null');
      } else {
        $this->constraints[$param1] = $param2;
      }
    }
  }

  public function setChildConstraint($param1, $param2 = null)
  {
    if (is_null($param2)) {
      if (!is_array($param1)) {
        throw new Exception('Error: setChildConstraint() when Argument 2 is null, Argument 1 must be an Array');
      } else {
        $this->defaultChildConstraints = $param1;
      }
    } else {
      if (!is_array($param2)) {
        throw new Exception('Error: setChildConstraint() Argument 2 must be an Array');
      } else {
        $this->childConstraints[$param1] = $param2;
      }
    }
  }

  protected function receiveChildConstraint(array $constraints)
  {
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
    if (empty($param1)) return;

    if ($this->isSpecialParam($param3, $param1)) {
      $values   = array();
      $values[] = $param2;
      $values[] = $param3;
      $this->conditions[$param1] = $values;
    } elseif ($this->isDefaultColumnValue($param2)) {
      $this->conditions[$this->defColumn] = $param1;
    } else {
      $this->conditions[$param1] = $param2;
    }
  }
  
  protected function isSpecialParam($param3, $param1)
  {
    return (!is_null($param3) && !is_array($param1));
  }
  
  protected function isDefaultColumnValue($param2)
  {
    return is_null($param2);
  }
  
  public function getCount($param1 = null, $param2 = null, $param3 = null)
  {
    $this->setCondition($param1, $param2, $param3);

    $this->edo->setBasicSQL("SELECT COUNT(*) FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, array('limit' => 1));

    if ($this->edo->execute()) {
      $row = $this->edo->fetch();
      return (int)$row[0];
    } else {
      throw new Exception('Error: getCount()');
    }
  }
  
  public function getNextNumber()
  {
    return $this->edo->getNextNumber($this->table);
  }

  public function aggregate($functions, $child = null)
  {
    if (is_null($child)) {
      $table    = $this->table;
      $idColumn = 'id';
    } else {
      $table    = $child;
      $idColumn = $this->table.'_id';
    }

    $this->edo->setAggregateSQL($table, $idColumn, $functions);
    $this->edo->makeQuery(null, $this->constraints);

    $recordObj = array();

    if ($this->edo->execute($sql)) {
      $rows = $this->edo->fetchAll(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      return $this->toObject($rows);
    } else {
      return false;
    }
  }

  public function defaultSelectOne($param1, $param2 = null)
  {
    $this->setCondition($param1, $param2);
    $this->selectCondition = $this->conditions;

    $this->makeFindObject($this);
  }

  public function selectOne($param1 = null, $param2 = null, $param3 = null)
  {
    if (is_null($param1) && is_null($this->conditions))
      throw new Exception('Error: selectOne() [WHERE] must be set condition');

    $this->setCondition($param1, $param2, $param3);
    $this->selectCondition = $this->conditions;

    return $this->makeFindObject(clone($this));
  }

  protected function makeFindObject($obj)
  {
    $this->edo->setBasicSQL("SELECT {$this->projection} FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    if ($this->edo->execute()) {
      $row = $this->edo->fetch(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      if ($row) {
        $row = $this->selectWithParent($this->selectType, $row);
        $obj->setProperties($row);
        $obj->selected = true;

        $myChild = $this->getMyChildren();
        if (!is_null($myChild)) $this->getDefaultChild($myChild, $obj);
      } else {
        $obj->data = $this->selectCondition;
      }
      $this->constraints = array();
      $this->conditions  = array();
      return $obj;
    } else {
      throw new Exception('Error: makeFindObject()');
    }
  }

  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    if (!empty($param1))
      $this->setCondition($param1, $param2, $param3);

    $this->edo->setBasicSQL("SELECT {$this->projection} FROM {$this->table}");
    return $this->getRecords($this->conditions, $this->constraints);
  }

  public function getChild($child, $obj = null)
  {
    if (is_null($obj)) $obj = $this;

    $obj->chooseMyChildConstraint($child, $obj);

    if (!isset($obj->childConstraints[$child]['limit']))
      throw new Exception('Error: getChildren() must be set limit constraints');

    $condition = array("{$obj->table}_id" => $obj->data[$obj->defColumn]);

    $obj->edo->setBasicSQL("SELECT * FROM {$child}");
    $obj->data[$child] = $obj->getRecords($condition, $obj->childConstraints[$child], $child);
  }

  protected function getRecords($conditions, $constraints = null, $child_table = null)
  {
    $this->edo->makeQuery($conditions, $constraints);

    $recordObj = array();
    $class     = get_class($this);

    if ($this->edo->execute()) {
      $rows = $this->edo->fetchAll(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      if (!$rows) return null;

      foreach ($rows as $row) {
        if (is_null($child_table)) {
          $obj = new $class();
          $obj->receiveChildConstraint($this->childConstraints);
        } else {
          if (class_exists($child_table)) {
            $obj = new $child_table();
          } else {
            $obj = new Child_Record($child_table);
          }
        }
        $row = $this->selectWithParent($this->selectType, $row);
        
        $obj->setProperties($row);
        $obj->selected = true;

        $myChild = $obj->getMyChildren();
        if (!is_null($myChild)) {
          if (!is_null($child_table)) $this->chooseMyChildConstraint($myChild, $obj);
          $this->getDefaultChild($myChild, $obj);
        }
        $recordObj[] = $obj;
      }
      $this->constraints = array();
      $this->conditions  = array();
      return $recordObj;
    } else {
      throw new Exception('Error: getRecords()');
    }
  }

  protected function getDefaultChild($children, $obj)
  {
    if (is_array($children)) {
      foreach ($children as $val) {
        $this->chooseMyChildConstraint($val, $obj);
        $obj->getChild($val, $obj);
      }
    } else {
      $this->chooseMyChildConstraint($children, $obj);
      $obj->getChild($children, $obj);
    }
  }

  private function chooseMyChildConstraint($child, $obj)
  {
    if (array_key_exists($child, $this->childConstraints)) {
      $constraints = $this->constraintMerge($child, $this->childConstraints[$child]);
      $obj->setChildConstraint($child, $constraints);
    } elseif (!empty($this->defaultChildConstraints)) {
      $obj->setChildConstraint($child, $this->defaultChildConstraints);
    } elseif ($constraints = $this->hasMyChildConstraint($child, $obj)) {
      $obj->setChildConstraint($child, $constraints);
    } else {
      $constraints = $this->hasDefaultChildConstraint($obj);
      $obj->setChildConstraint($child, $constraints);
    }
    $obj->defaultChildConstraints = $this->defaultChildConstraints;
  }
  
  private function constraintMerge($child, $constraints)
  {
    if ($result = $this->hasMyChildConstraint($child, $this)) {
      foreach ($result as $key => $val) {
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
    if (!empty($obj->defaultChildConstraints)) {
      return $obj->defaultChildConstraints;
    } else { 
      throw new Exception('Error: constraint of child object, not found.');
    }
  }

  protected function selectWithParent($type, $row)
  {
    foreach ($row as $key => $val) {
      if (strpos($key, '_id')) {
        $table = str_replace('_id', '', $key);

        if ($type == self::WITH_PARENT_VIEW) {
          $this->parentTables[] = $this->table;
          $this->addParentProperties($table, $val, $row);
        } elseif ($type == self::WITH_PARENT_OBJECT) {
          $this->parentTables[] = $this->table;
          $row[$table] = $this->addParentObject($table, $val);
        } else {
          // ignore 
        }
      }
    }
    return $row;
  }

  protected function addParentProperties($table, $id, &$row)
  {
    if ($this->getStructure() != 'tree') {
      if ($this->isAcquiredObject($table)) return null;
    }

    $edo = $this->getMyEDO();
    $edo->setBasicSQL("SELECT * FROM {$table}");
    $edo->makeQuery(array($this->defColumn => $id));

    if ($edo->execute()) {
      $prow = $edo->fetch(Sabel_Edo_Driver_Interface::FETCH_ASSOC);

      if (!$prow) return;

      foreach ($prow as $key => $val) {
        if (strpos($key, '_id')) {
          $ptable = str_replace('_id', '', $key);
          $row["{$table}_{$key}"] = $val;
          $this->addParentProperties($ptable, $val, $row);
        } else {
          $row["{$table}_{$key}"] = $val;
        }
      }
    } else {
      throw new Exception('Error: addParentProperties()');
    }
  }

  protected function addParentObject($table, $id)
  {
    /*
    // @todo fix me please (〃▽〃)ｷｬｰ♪
    if ($this->getStructure() != 'tree') {
      if ($this->isAcquiredObject($table)) {
        return null;
      }
    }
    */

    $edo = $this->getMyEDO();
    $edo->setBasicSQL("SELECT * FROM {$table}");
    $edo->makeQuery(array($this->defColumn => $id));

    if ($edo->execute()) {
      $row = $edo->fetch(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      $obj = $this->newClass($table);

      if (!$row) return $obj;

      $obj->selectCondition[$this->defColumn] = $id;
      $obj->selected = true;

      foreach ($row as $key => $val) {
        if (strpos($key, '_id')) {
          $key = str_replace('_id', '', $key);
          $row[$key] = $this->addParentObject($key, $val);
        } else {
          $row[$key] = $val;
        }
      }
      $obj->setProperties($row);
      $obj->newData = array();
      return $obj;
    } else {
      throw new Exception('Error: addParentObject()');
    }
  }

  protected function isAcquiredObject($table)
  {
    for ($i = 0; $i < count($this->parentTables); $i++) {
      if ($this->parentTables[$i] == $table) return true;
    }
    $this->parentTables[] = $table;
    return false;
  }

  public function newChild($child = null)
  {
    $id = $this->data[$this->defColumn];
    if (empty($id))
      throw new Exception('Error: who is a parent? hasn\'t id value.');

    $parent = strtolower(get_class($this));
    $table = (is_null($child)) ? $parant : $child;

    $obj = $this->newClass($table);

    $column = $parent.'_id';
    $obj->$column = $id;
    return $obj;
  }

  protected function newClass($name)
  {
    if (class_exists($name) && $name != 'Sabel_Edo_CommonRecord') {
      return new $table();
    } else {
      return new Sabel_Edo_CommonRecord($name);
    }
  }

  public function killAll($child)
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
    $this->dataMerge($data);

    if ($this->selected) {
      $this->update();
    } else {
      return $this->insert();
    }
  }

  public function allUpdate($data = null)
  {
    $this->dataMerge($data);

    $this->edo->setUpdateSQL($this->table, $this->data);
    $this->edo->makeQuery($this->conditions);

    if ($this->edo->execute()) {
      $this->conditions = array();
    } else {
      throw new Exception('Error: allUpdate()');
    }
  }

  protected function update()
  {
    $this->edo->setUpdateSQL($this->table, $this->newData);
    $this->edo->makeQuery($this->selectCondition);

    if ($this->edo->execute()) {
      $this->selectCondition = array();
    } else {
      throw new Exception('Error: update()');
    }
  }

  protected function insert()
  {
    if ($this->edo->executeInsert($this->table, $this->data, isset($this->data['id']))) {
      return $this->edo->getLastInsertId();
    } else {
      throw new Exception('Error: insert()');
    }
  }

  public function multipleInsert($data)
  {
    if (!is_array($data))
      throw new Exception('Error: data is not array.');

    if (!$this->edo->executeInsert($this->table, $data, true)) {
      throw new Exception('Error: multipleInsert()');
    }
  }

  protected function dataMerge($data)
  { 
    if (empty($data)) return;

    foreach ($data as $key => $val) {
      if (array_key_exists($key, $this->data)) {
        throw new Exception("Error: [\'{$key}\'] is already set!");
      } else {
        $this->data[$key] = $val;
      }
    }
  }

  public function remove($param1 = null, $param2 = null, $param3 = null)
  {
    if (is_null($param1) && is_null($this->conditions))
      throw new Exception("Error: remove() [WHERE] must be set condition");

    if (!is_null($param1))
      $this->setCondition($param1, $param2, $param3);

    $this->edo->setBasicSQL("DELETE FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    if ($this->edo->execute()) {
      $this->conditions  = array();
      $this->constraints = array();
    } else {
      throw new Exception('Error: remove()');
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
    $recordObj = array();
    $class = get_class($this);

    foreach ($array as $row) {
      $obj = $this->newClass($class);
      $obj->setProperties($row);
      $recordObj[] = $obj;
    }
    return $recordObj;
  }
}

class Sabel_Edo_CommonRecord extends Sabel_Edo_RecordObject
{
  public function __construct($table = null)
  {
    $this->setEDO('user', 'pdo');
    parent::__construct();

    if (!is_null($table)) $this->table = $table;
  }
}
