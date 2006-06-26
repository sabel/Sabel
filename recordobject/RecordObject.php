<?php

require_once('EDO.php');
require_once('PdoEDO.php');
require_once('SQLObject.php');

abstract class RecordObject
{
  protected
    $constraints = array(),
    $conditions  = array();

  protected
    $edo,
    $table,
    $defColumn;

  public function makePDO($site)
  {
    $this->edo = new PdoEDO($site);
  }

  public function __construct($defColumn = null, $_table = null)
  {
    if (!is_null($defColumn)) {
      $this->defColumn = $defColumn;
		}
		
    $this->table = (is_null($_table)) ? strtolower(get_class($this)) : $_table;
  }

  public function __set($key, $val)
  {
    $this->$key = $val;
  }
  
  public function setPropertys($array)
  {
    foreach ($array as $key => $val) {
      $this->$key = $val;
    }    
  }

  public function setConstraint($param1, $param2 = null)
  {
    if (is_array($param1)) {
      foreach ($param1 as $key => $val) {
        if (is_null($val)) {
          echo 'Error: constraint value is null';
          exit;
        } else {
          $this->constraints["{$key}"] = $val;
        }
      }
    } else {
      if (is_null($param2)) {
        echo 'Error: constraint value is null';
        exit;
      } else {
        $this->constraints["{$param1}"] = $param2;
      }
    }
  }
  
  public function setCondition($param1, $param2 = null)
  {
    if (is_array($param1)) {
      foreach ($param1 as $key => $val) {
        if (strtolower($val) == 'null') $val = null;
        $this->conditions["{$key}"] = $val;
      }
    } else {
      if (!is_null($param1)) {
        if (strtolower($param2) == 'null') $param2 = null;
     	  $this->conditions["{$param1}"] = $param2;
      }
    }
  }
  
  public function getCount($conditions = null)
  {
    $this->addConditions($conditions);

    $this->edo->setBasicSQL("SELECT COUNT(*) AS count FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    if ($this->edo->execute()) {
      $row = $this->edo->fetch();
      return $row[0];
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

  public function selectOne($conditions = null)
  {
    if (is_null($conditions) && is_null($this->conditions)) {
      echo "Error: selectOne() [WHERE] must be set condition";
      exit;
    }
    $this->addConditions($conditions);

    $this->edo->setBasicSQL("SELECT * FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    if ($this->edo->execute()) {
      $row = $this->edo->fetch(EDO::FETCH_ASSOC);
      if ($row) {
        $this->setPropertys($row);
        return $this;
      } else {
        return false;
      }
    }
  }

  public function select($conditions = null)
  {
    $this->addConditions($conditions);

    $this->edo->setBasicSQL("SELECT * FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    $recordObj = array();
    $class = get_class($this);

    if ($this->edo->execute()) {
      while ($row = $this->edo->fetch(EDO::FETCH_ASSOC)) {
        $obj = new $class();
        $obj->setPropertys($row);
        $recordObj[] = $obj;
      }
      return $recordObj;
    } else {
      echo 'Error: select()';
      exit;
    }
  }

  protected function addConditions($conditions = null)
  {
    if (is_array($conditions)) {
        $this->setCondition($conditions);
    } else {
      if (!is_null($conditions)) {
        $this->setCondition($this->defColumn, $conditions);
      }
    }
  }

  public function insert($data)
  {
    $this->edo->setInsertSQL($this->table, $data);
    $this->edo->makeQuery();

    if (!$this->edo->execute()) {
      echo 'Error: insert()';
    } 
  }
  
  public function update($data, $conditions = null)
  {
    $this->addConditions($conditions);

    $this->edo->setUpdateSQL($this->table, $data);
    $this->edo->makeQuery($this->conditions);

    if (!$this->edo->execute()) {
      echo 'Error: update() ';
    } 
  }

  protected function executePreparedSQL($data, $conditions = null)
  {
    //todo
  }

  public function delete($conditions = null)
  {
    if (is_null($conditions) && is_null($this->conditions)) {
      $className = get_class($this);
      echo "Error: {$className}::delete() [WHERE] must be set condition";
      exit;
    }
    $this->addConditions($conditions);

    $this->edo->setBasicSQL("DELETE FROM {$this->table}");
    $this->edo->makeQuery($this->conditions, $this->constraints);

    if (!$this->edo->execute()) {
      echo 'Error: delete()';
    } 
  }

  public function execute($sql)
  {
    $recordObj = array();
    $class = get_class($this);

    if ($this->edo->execute($sql)) {
      while ($row = $this->edo->fetch(EDO::FETCH_ASSOC)) {
        $obj = new $class();
        $obj->setPropertys($row);
        $recordObj[] = $obj;
      }
      return $recordObj;
    } else {
      return false;
    }
  }
}

?>
