<?php

uses('sabel.edo.DBConnection');
uses('sabel.edo.RecordClasses');

uses('sabel.edo.driver.Pdo');
uses('sabel.edo.driver.Mysql');
uses('sabel.edo.driver.Pgsql');
/*
$parent = new Parent();
$child = $parent->newChild();
$child->name = 'tanaka';
$child->save();
*/

abstract class Sabel_Edo_RecordObject
{
  protected
    $constraints      = array(),
    $childConstraints = array(),
    $conditions       = array(),
    $selectCondition  = array();
    
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
      $pdoDb = Sabel_Edo_DBConnection::getPdoDB($this->owner);
      return new Sabel_Edo_Driver_Pdo($conn, $pdoDb);
    } elseif ($this->useEdo == 'pgsql') {
      return new Sabel_Edo_Driver_Pgsql($conn);
    } elseif ($this->useEdo == 'mysql') {
      return new Sabel_Edo_Driver_Mysql($conn);
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
      $pdoDb = Sabel_Edo_DBConnection::getPdoDB($owner);
      $this->edo = new Sabel_Edo_Driver_Pdo($conn, $pdoDb);
    } elseif ($useEdo == 'pgsql') {
      $this->edo = new Sabel_Edo_Driver_Pgsql($conn);
    } elseif ($useEdo == 'mysql') {
      $this->edo = new Sabel_Edo_Driver_Mysql($conn);
    } else {
      //todo
    }
  }

  public function __construct($param1 = null, $param2 = null)
  {
    $this->table = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', get_class($this)));

    if (!is_null($param1))
      $this->selectOne($param1, $param2);
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
    $this->childConstraints = array();

    if (is_array($param1)) {
      foreach ($param1 as $key => $val) {
        if (is_null($val)) {
          throw new Exception('Error: setChildConstraint() constraint value is null');
        } else {
          $this->childConstraints[$key] = $val;
        }
      }
    } else {
      if (is_null($param2)) {
        throw new Exception('Error: setChildConstraint() constraint value is null');
      } else {
        $this->childConstraints[$param1] = $param2;
      }
    }
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
      $values = array();
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

    $this->edo->setBasicSQL("SELECT COUNT(*) AS count FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    if ($this->edo->execute()) {
      $row = $this->edo->fetch();
      return (int)$row[0];
    }
  }
  
  public function getNextNumber($column = null)
  {
    $col = (is_null($column)) ? $this->defColumn : $column;
    $sql = "SELECT {$col} FROM {$this->table} ORDER BY {$col} desc";
    
    if ($this->edo->execute($sql)) {
      $row = $this->edo->fetch();
    
      if (is_null($row[0])) {
        return 1;
      } else {    
        return $row[0] + 1;
      }
    } else {
      return false;
    }
  }

  public function selectOne($param1 = null, $param2 = null, $param3 = null)
  {
    if (is_null($param1) && is_null($this->conditions)) {
      throw new Exception('Error: selectOne() [WHERE] must be set condition');
    }
    $this->setCondition($param1, $param2, $param3);
    $this->selectCondition = $this->conditions;

    $this->edo->setBasicSQL("SELECT {$this->projection} FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    if ($this->edo->execute()) {
      $row = $this->edo->fetch(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      if ($row) {
        $row = $this->selectWithParent($this->selectType, $row);
        $this->setProperties($row);
        $this->selected = true;

        $myChild = $this->getMyChildren();
        if (!is_null($myChild)) $this->getChild($myChild, $this);
        return $this;
      } else {
        $this->data = $this->selectCondition;
        return $this;
      }
    }
  }

  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    if (!empty($param1))
      $this->setCondition($param1, $param2, $param3);

    $this->edo->setBasicSQL("SELECT {$this->projection} FROM {$this->table}");
    return $this->getRecords($this->conditions, $this->constraints, $this->childConstraints);
  }

  public function getChild($child_table, $obj)
  {
    if (!isset($obj->childConstraints['limit']))
      throw new Exception('Error: getChildren() [LIMIT] must be set constraints');

    $condition = array("{$obj->table}_id" => $obj->data[$obj->defColumn]);

    $obj->edo->setBasicSQL("SELECT * FROM {$child_table}");
    $obj->data[$child_table] = $obj->getRecords($condition, $obj->childConstraints, $child_table);
  }

  protected function getRecords(&$conditions, &$constraints = null, $param = null)
  {
    $this->edo->makeQuery($conditions, $constraints);

    $recordObj = array();
    $class     = get_class($this);

    if ($this->edo->execute()) {
      while ($row = $this->edo->fetch(Sabel_Edo_Driver_Interface::FETCH_ASSOC)) {
        if (is_array($param)) {
          $obj = new $class();
          $obj->setChildConstraint($param);
        } else {
          if (class_exists($param)) {
            $obj = new $param();
          } else {
            $obj = new Child_Record($param);
          }
        }
        $row = $this->selectWithParent($this->selectType, $row);
        $obj->setProperties($row);
        $obj->selected = true;

        $myChild = $obj->getMyChildren();
        if (!is_null($myChild)) $obj->getChild($myChild, $obj);
        $recordObj[] = $obj;
      }
      return $recordObj;
    } else {
      throw new Exception('Error: getRecords()');
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
    if ($this->hasAlreadyAcquiredParent($table)) return null;

    $edo = $this->makeBasicQueryForChild($table, $id);
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
    if ($this->hasAlreadyAcquiredParent($table)) return null;

    $edo = $this->makeBasicQueryForChild($table, $id);
    if ($edo->execute()) {
      $row = $edo->fetch(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      if (class_exists($table)) {
        $obj = new $table();
      } else {
        $obj = new Common_Record($table);
      }

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

  protected function makeBasicQueryForChild($table, $id)
  {
    $condition  = array($this->defColumn => $id);

    $edo = $this->getMyEDO();
    $edo->setBasicSQL("SELECT * FROM {$table}");
    $edo->makeQuery($condition);
    return $edo;
  }

  protected function hasAlreadyAcquiredParent($table)
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

    if (class_exists($table)) {
      $obj = new $table();
    } else {
      $obj = new Common_Record($table);
    }
    $obj->__set("{$parent}_id", $id);
    return $obj;
  }

  public function save($data = null)
  {
    $this->dataMerge($data);

    if ($this->selected) {
      $this->update();
    } else {
      $this->insert();
    }
  }

  public function allUpdate($data = null)
  {
    $this->dataMerge($data);

    $this->edo->setUpdateSQL($this->table, $this->data);
    $this->edo->makeQuery($this->conditions);

    if (!$this->edo->execute()) {
      throw new Exception('Error: allUpdate()');
    }
  }

  protected function update()
  {
    $this->edo->setUpdateSQL($this->table, $this->newData);
    $this->edo->makeQuery($this->selectCondition);

    if (!$this->edo->execute()) {
      throw new Exception('Error: update()');
    } 
  }

  protected function insert()
  {
    if (!$this->edo->executeInsert($this->table, $this->data)) {
      throw new Exception('Error: insert()');
    }
  }

  public function multipleInsert($data)
  {
    if (!is_array($data))
      throw new Exception('Error: data is not array.');

    if (!$this->edo->executeInsert($this->table, $data)) {
      throw new Exception('Error: multipleInsert()');
    }
  }

  protected function dataMerge($data)
  { 
    if (empty($data)) return;

    foreach ($data as $key => $val) {
      if (array_key_exists($key, $this->data)) {
        throw new Exception("Error: [{$key}] is already set!");
      } else {
        $this->data[$key] = $val;
      }
    }
  }

  public function delete($param1 = null, $param2 = null, $param3 = null)
  {
    if (is_null($param1) && is_null($this->conditions)) {
      $className = get_class($this);
      throw new Exception("Error: {$className}::delete() [WHERE] must be set condition");
    }

    if (!is_null($param1))
      $this->setCondition($param1, $param2, $param3);

    $this->edo->setBasicSQL("DELETE FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    if (!$this->edo->execute()) {
      throw new Exception('Error: delete()');
    } 
  }

  public function execute($sql)
  {
    $recordObj = array();
    $class = get_class($this);

    if ($this->edo->execute($sql)) {
      while ($row = $this->edo->fetch(Sabel_Edo_Driver_Interface::FETCH_ASSOC)) {
        $obj = new $class();
        $obj->setProperties($row);
        $recordObj[] = $obj;
      }
      return $recordObj;
    } else {
      return false;
    }
  }
}

?>
