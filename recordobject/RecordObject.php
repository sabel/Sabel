<?php

require_once('EDO.php');
require_once('PdoEDO.php');
require_once('RecordClasses.php');

abstract class RecordObject
{
  protected
    $constraints      = array(),
    $childConstraints = array(),
    $conditions       = array(),
    $selectCondition  = array();
    
  protected
    $edo,
    $type = 'pdo',
    $table,
    $defColumn = 'id';

  protected 
    $data     = array(),
    $newData  = array(),
    $selected = false;

  protected static $conList = array();

  protected $selectType = self::SELECT_DEFAULT;

  const SELECT_DEFAULT = 0;
  const SELECT_VIEW    = 5;
  const SELECT_CHILD   = 10;

  public static function addCon($type, $con)
  {
    self::$conList[$type] = $con;
  }

  public function getEDO()
  {
    if ($this->type == 'pdo') {
      return new PdoEDO(self::$conList['pdo']);
    } elseif ($this->type == 'pgsql') {
      return new PGEDO(self::$conList['pgsql']);
    } elseif ($this->type == 'mysql') {
      return new MYEDO(self::$conList['mysql']);
    } else {
      //todo
    }
  }

  public function setEDO($type)
  {
    $this->type = $type;

    if ($type == 'pdo') {
      $this->edo = new PdoEDO(self::$conList['pdo']);
    } elseif ($type == 'pgsql') {
      $this->edo = new PGEDO(self::$conList['pgsql']);
    } elseif ($type == 'mysql') {
      $this->edo = new MYEDO(self::$conList['mysql']);
    } else {
      //todo
    }
  }

  public function __construct($param1 = null, $param2 = null)
  {
    $this->table = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', get_class($this)));
    $this->setEDO($this->type);

    if (!is_null($param1)) {
      if (!is_null($param2)) {
        $this->setColumn($param1);
        $this->selectOne($param2);
      } else {
        $this->selectOne($param1);
      }
    }
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

  public function setColumn($column)
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
      foreach ($param1 as $key => $vphal) {
        if (is_null($val)) {
          throw new Exception('Error: setConstraint() constraint value is null');
        } else {
          $this->constraints["{$key}"] = $val;
        }
      }
    } else {
      if (is_null($param2)) {
        throw new Exception('Error: setConstraint() constraint value is null');
      } else {
        $this->constraints["{$param1}"] = $param2;
      }
    }
  }

  public function setChildConstraint($param1, $param2 = null)
  {
    if (is_array($param1)) {
      foreach ($param1 as $key => $val) {
        if (is_null($val)) {
          throw new Exception('Error: setChildConstraint() constraint value is null');
        } else {
          $this->childConstraints["{$key}"] = $val;
        }
      }
    } else {
      if (is_null($param2)) {
        throw new Exception('Error: setChildConstraint() constraint value is null');
      } else {
        $this->childConstraints["{$param1}"] = $param2;
      }
    }
  }

  public function setCondition($param1, $param2 = null, $param3 = null)
  {
    if (empty($param1)) return;

    if (!is_null($param3)) {
      if (is_array($param1)) {
        throw new Exception('EDO Error');
      } else {
        $values = array();
        $values[] = $param2;
        $values[] = $param3;
        $this->conditions["{$param1}"] = $values;
      }
    } else {
      if (is_array($param1)) {
        foreach ($param1 as $key => $val) { 
          $this->conditions["{$key}"] = $val;
        }
      } else {
        if (is_null($param2)) {
          $this->conditions["{$this->defColumn}"] = $param1;
        } else {
   	      $this->conditions["{$param1}"] = $param2;
        }
      }
    }
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
    }
  }

  public function selectOne($param1 = null, $param2 = null, $param3 = null)
  {
    if (is_null($param1) &&is_null($this->conditions)) {
      throw new Exception('Error: selectOne() [WHERE] must be set condition');
    }

    $this->setCondition($param1, $param2, $param3);
    $this->selectCondition = $this->conditions;

    $this->edo->setBasicSQL("SELECT * FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    if ($this->edo->execute()) {
      $row = $this->edo->fetch(EDO::FETCH_ASSOC);
      if ($row) {
        if ($this->selectType == self::SELECT_VIEW) {
          foreach ($row as $key => $val) {
            if (strpos($key, '_id')) {
              $table = str_replace('_id', '', $key);

              $row["{$this->table}_{$key}"] = $val;
              unset($row[$key]);

              $this->join($table, $val, $row);
            } else {
              $row["{$this->table}_{$key}"] = $val;
              unset($row[$key]);
            }
          }
        } elseif ($this->selectType == self::SELECT_CHILD) {
          foreach ($row as $key => $val) {
            if (strpos($key, '_id')) {
              $key = str_replace('_id', '', $key);
              $row[$key] = $this->getChild($key, $val);
            }
          }
        } else {
          // none
        }
        $this->setProperties($row);
        $this->selected = true;
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

    $this->edo->setBasicSQL("SELECT * FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    $class = get_class($this);

    if ($this->edo->execute()) {
      if ($this->selectType == self::SELECT_DEFAULT) {
        return $this->selectDefault($class);
      } elseif ($this->selectType == self::SELECT_VIEW) {
        return $this->selectView($class);
      } elseif ($this->selectType == self::SELECT_CHILD) {
        return $this->selectChild($class);
      } else {
        throw new Exception('invalid RecordObject::SELECT_TYPE');
      }
    } else {
      throw new Exception('Error: select()');
    }
  }

  protected function selectDefault($class)
  {
    $recordObj = array();

    while ($row = $this->edo->fetch(EDO::FETCH_ASSOC)) {
      $obj = new $class();
      $obj->setProperties($row);
      $recordObj[] = $obj;
    }
    return $recordObj;
  }

  protected function selectView($class)
  {
    $recordObj = array();

    while ($row = $this->edo->fetch(EDO::FETCH_ASSOC)) {
      $obj = new $class();

      foreach ($row as $key => $val) {
        if (strpos($key, '_id')) {
          $table = str_replace('_id', '', $key);

          $row["{$this->table}_{$key}"] = $val;
          unset($row[$key]);

          $this->join($table, $val, $row);
        } else {
          $row["{$this->table}_{$key}"] = $val;
          unset($row[$key]);
        }
      }
      $obj->setProperties($row);
      $recordObj[] = $obj;
    }
    return $recordObj;
  }

  protected function join($table, $id, &$row)
  {
    $condition  = array($this->defColumn => $id);
    
    $edo = $this->getEDO();
    $edo->setBasicSQL("SELECT * FROM {$table}");
    $edo->makeQuery($condition);

    $children = array();

    if ($edo->execute()) {
      while ($crow = $edo->fetch(EDO::FETCH_ASSOC)) {
        $obj = new Child_Record();
        foreach ($crow as $key => $val) {
          if (strpos($key, '_id')) {
            $ctable = str_replace('_id', '', $key);
            $row["{$table}_{$key}"] = $val;
            $this->join($ctable, $val, $row);
          } else {
            $row["{$table}_{$key}"] = $val;
          }
        }
        $obj->setProperties($row);
        $children[] = $obj;
      }
      return $children;
    } else {
      throw new Exception('Error: join()');
    }
  }

  protected function selectChild($class)
  {
    $recordObj = array();

    while ($row = $this->edo->fetch(EDO::FETCH_ASSOC)) {
      $obj = new $class();
      foreach ($row as $key => $val) {
        if (strpos($key, '_id')) {
          $key = str_replace('_id', '', $key);
          $row[$key] = $this->getChild($key, $val);
        }
      }
      $obj->setProperties($row);
      $recordObj[] = $obj;
    }
    return $recordObj;
  }

  protected function getChild($table, $id)
  {
    $condition  = array($this->defColumn => $id);
    $constraint = $this->childConstraints;

    $edo = $this->getEDO();

    $edo->setBasicSQL("SELECT * FROM {$table}");
    $edo->makeQuery($condition, $constraint);

    $children = array();

    if ($edo->execute()) {
      while ($row = $edo->fetch(EDO::FETCH_ASSOC)) {
        $obj = new Child_Record();
        foreach ($row as $key => $val) {
          if (strpos($key, '_id')) {
            $key = str_replace('_id', '', $key);
            $row[$key] = $this->getChild($key, $val);
          }
        }
        $obj->setProperties($row);
        $children[] = $obj;
      }
      return $children;
    } else {
      throw new Exception('Error: getChild()');
    }
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

  public function update()
  {
    $this->edo->setUpdateSQL($this->table, $this->newData);
    $this->edo->makeQuery($this->selectCondition);

    if (!$this->edo->execute()) {
      throw new Exception('Error: update()');
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

  public function insert()
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
      //echo 'Error: delete()';
    } 
  }

  public function execute($sql)
  {
    $recordObj = array();
    $class = get_class($this);

    if ($this->edo->execute($sql)) {
      while ($row = $this->edo->fetch(EDO::FETCH_ASSOC)) {
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

class Child_Record extends RecordObject
{
}

?>
