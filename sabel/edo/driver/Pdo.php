<?php

//uses('sabel.edo.driver.Interface');
//uses('sabel.edo.query.php');

class Sabel_Edo_Driver_Pdo implements Sabel_Edo_Driver_Interface
{
  private $pdo, $stmt, $sqlObj, $myDb;

  private $param = array();
  private $data  = array();

  public function __construct($conn, $myDb)
  {
    $this->pdo    = $conn;
    $this->myDb   = $myDb;
    $this->sqlObj = new PdoQuery();
  }

  public function begin()
  {
    //@todo
  }

  public function commit()
  {
    //@todo
  }

  public function setBasicSQL($sql)
  {
    $this->sqlObj->setBasicSQL($sql);
  }

  public function setUpdateSQL($table, $data)
  {
    $this->data = $data;

    $sql = array("UPDATE {$table} SET");
    $set = false;

    foreach ($data as $key => $val) {
      if (!$set) {
        array_push($sql, " {$key}=:{$key}");
        $set = true;
      } else {
        array_push($sql, ",{$key}=:{$key}");
      }
    }

    $this->sqlObj->setBasicSQL(implode('', $sql));
  }

  public function setAggregateSQL($table, $idColumn, $functions)
  {
    $sql = array("SELECT {$idColumn}");

    foreach ($functions as $key => $val)
      array_push($sql, ", {$key}({$val}) AS {$key}_{$val}");

    array_push($sql, " FROM {$table} GROUP BY {$idColumn}");
    $this->sqlObj->setBasicSQL(implode('', $sql));
  }

  public function executeInsert($table, $data, $id_exist = null)
  {
    if (!$id_exist && $this->myDb === 'pgsql')
      $data['id'] = $this->getNextNumber($table);

    $this->data = $data;

    if ($table === 'order_line') $this->disp = true;

    $sql = array("INSERT INTO {$table}(");
    $set = false;

    foreach ($data as $key => $val) {
      if (!$set) {
        array_push($sql, "{$key}");
        $set = true;
      } else {
        array_push($sql, ",{$key}");
      }
    }

    array_push($sql, ") VALUES(");
    $set = false;

    foreach ($data as $key => $val) {
      if (!$set) {
        array_push($sql, ":{$key}");
      } else {
        array_push($sql, ",:{$key}");
      }
      $set = true;
    }

    array_push($sql, ');');

    $this->stmtFlag = Sabel_Edo_Driver_PdoStatement::statement_exists(implode('', $sql), $data);

    if (!$this->stmtFlag)
      $this->sqlObj->setBasicSQL(implode('', $sql));

    return $this->execute();
  }

  public function getLastInsertId()
  {
    if ($this->myDb === 'pgsql') {
      return $this->lastInsertId;
    } elseif ($this->myDb === 'mysql') {
      $this->execute('SELECT last_insert_id()');
      $row = $this->fetch(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      return $row['last_insert_id()'];
    } else {
      return 'todo else';
    }
  }

  private function getNextNumber($table)
  {
    if ($this->myDb === 'pgsql') {
      $this->execute('SELECT nextval(\'' . $table . '_id_seq\');');
      $row = $this->fetch();
      if (($this->lastInsertId =(int) $row[0]) === 0) {
        throw new Exception($table.'_id_seq is not found.');
      } else {
        return $this->lastInsertId;
      }
    } else {
      return 'todo else';
    }
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $this->stmtFlag = Sabel_Edo_Driver_PdoStatement::statement_exists($this->sqlObj->getSQL(), $conditions, $constraints);

    if (!empty($conditions)) {
      foreach ($conditions as $key => $val) {
        $check = false;
        if ($val[0] == '>' || $val[0] == '<') {
          $this->sqlObj->makeLess_GreaterSQL($key, $val);
        } elseif (strstr($key, Sabel_Edo_Driver_Interface::IN)) {
          $key = str_replace(Sabel_Edo_Driver_Interface::IN, '', $key);
          $this->sqlObj->makeWhereInSQL($key, $val);
        } elseif (strstr($key, Sabel_Edo_Driver_Interface::BET)) {
          $key = str_replace(Sabel_Edo_Driver_Interface::BET, '', $key);
          $this->sqlObj->makeBetweenSQL($key, $val);
        } elseif (strstr($key, Sabel_Edo_Driver_Interface::EITHER)) {
          $key = str_replace(Sabel_Edo_Driver_Interface::EITHER, '', $key);
          $this->sqlObj->makeEitherSQL($key, $val);
        } elseif (strstr($key, Sabel_Edo_Driver_Interface::LIKE)) {
          $key = str_replace(Sabel_Edo_Driver_Interface::LIKE, '', $key);
          $this->sqlObj->makeLikeSQL($key, $val);
        } elseif (strtolower($val) == 'null') {
          $this->sqlObj->makeIsNullSQL($key);
        } elseif (strtolower($val) == 'not null') {
          $this->sqlObj->makeIsNotNullSQL($key);
        } else {
          $this->sqlObj->makeNormalConditionSQL($key, $val);
          $check = true;
        }
        if (!$check) $this->stmtFlag = false;
      }
    }

    if (!empty($constraints) && !($this->stmtFrag))
      $this->sqlObj->makeConstraintsSQL($constraints);
  }

  public function execute($sql = null)
  {
    if (isset($sql)) {
      $this->stmt = $this->pdo->prepare($sql);
    } elseif ($this->stmtFlag) {
      $this->stmt = Sabel_Edo_Driver_PdoStatement::getStatement();
      Sabel_Edo_Driver_PdoStatement::$count++;
    } elseif (is_null($sql) && is_null($this->sqlObj->getSQL())) {
      print_r('Error: query not exist. execute EDO::makeQuery() beforehand');
    } else {
      $sql = $this->sqlObj->getSQL();
      if ($this->stmt = $this->pdo->prepare($sql)) {
        Sabel_Edo_Driver_PdoStatement::addStatement($this->stmt);
      } else {
        print_r('Error: PDOStatement is null.');
        print_r($sql."\n");
        print_r($this->pdo->errorInfo());
      }
    }

    $this->makeBindParam();
    //echo "{$this->stmt->queryString}\n";

    if (empty($this->param)) {
      $result = $this->stmt->execute();
    } else {
      $result = $this->stmt->execute($this->param);
      $this->param = array();
    }
    Sabel_Edo_Driver_PdoStatement::$count1++;

    if (!$result) {
      print_r('Error: PDOStatement::execute()');
      print_r($this->stmt);
      print_r($this->param);
      print_r($this->stmt->errorInfo());
    } else {
      return true;
    }
  }

  public function fetch($style = null)
  {
    if ($style == Sabel_Edo_Driver_Interface::FETCH_ASSOC) {
      $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      $result = $this->stmt->fetch(PDO::FETCH_BOTH);
    }
    $this->stmt->closeCursor();
    return $result;
  }

  public function fetchAll($style = null)
  {
    if ($style == Sabel_Edo_Driver_Interface::FETCH_ASSOC) {
      return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $this->stmt->fetchAll(PDO::FETCH_BOTH);
    }
  }

  private function makeBindParam()
  {
    $this->param = $this->sqlObj->getParam();

    if (!empty($this->param) && !empty($this->data)) {
      $this->param = array_merge($this->param, $this->data);
    } else {
      if (!empty($this->data)) $this->param = $this->data;
    }

    if (!empty($this->param)) {
      foreach ($this->param as $key => $val) {
        if (is_null($val)) continue;
        
        $this->param[":{$key}"] = $val;
        unset($this->param["{$key}"]);
      }
    }

    $this->data  = array();
    $this->sqlObj->unsetProparties();
  }
}

class PdoQuery 
{
  private $sql, $set;

  private $keyArray = array();
  private $param    = array();

  public function getSQL()
  {
    if (is_array($this->sql))
      return implode('', $this->sql);
  }

  public function setBasicSQL($sql)
  {
    $this->sql = array($sql);
  }

  protected function bindKey_exists($key)
  {
    $count =& $this->keyArray[$key]['count'];
    $count = (array_key_exists($key, $this->keyArray)) ? $count + 1 : 2;

    return $key . $count;
  }

  public function makeNormalConditionSQL($key, $val)
  {
    $bindKey = $this->bindKey_exists($key);
    $this->setWhereQuery("{$key}=:{$bindKey}");
    $this->param[$bindKey] = $val;
  }

  public function makeIsNullSQL($key)
  {
    $this->setWhereQuery($key . ' IS NULL');
  }

  public function makeIsNotNullSQL($key)
  {
    $this->setWhereQuery($key . ' IS NOT NULL');
  }

  public function makeWhereInSQL($key, $val)
  {
    $this->setWhereQuery($key . ' IN (' . implode(',', $val) . ')');
  }

  public function makeLikeSQL($key, $val)
  {
    $bindKey = $this->bindKey_exists($key);
    $this->setWhereQuery("{$key} LIKE :{$bindKey}");
    $this->param[$bindKey] = str_replace('_', '\_', $val); 
  }

  public function makeBetweenSQL($key, $val)
  {
    $this->setWhereQuery("{$key} BETWEEN :from AND :to");
    $this->param["from"] = $val[0];
    $this->param["to"]   = $val[1];
  }

  public function makeEitherSQL($key, $val)
  {
    $bindKey  = $this->bindKey_exists($key);
    $bindKey2 = $this->bindKey_exists($key);

    $val1 = $val[0];
    $val2 = $val[1];

    $query = '(';
    if ($val1[0] === '<' || $val1[0] === '>') {
      $query .= "{$key} ${val1[0]} :{$bindKey}";
      $this->param[$bindKey]  = trim(substr($val1, 1));
    } else if ($val1 === 'null') {
      $query .= "{$key} IS NULL";
    } else {
      $query .= "{$key}=:{$bindKey}";
      $this->param[$bindKey]  = $val1;
    }
    
    $query .= ' OR ';

    if ($val2[0] === '<' || $val2[0] === '>') {
      $query .= "{$key} {$val2[0]} :{$bindKey2}";
      $this->param[$bindKey2] = trim(substr($val2, 1));
    } else if ($val2 === 'null') {
      $query .= "{$key} IS NULL";
    } else {
      $query .= "{$key}=:{$bindKey2}";
      $this->param[$bindKey2] = $val2;
    }
    $query .= ')';
    
    $this->setWhereQuery($query);
  }

  public function makeLess_GreaterSQL($key, $val)
  {
    $bindKey = $this->bindKey_exists($key);
    $this->setWhereQuery("{$key} {$val[0]} :{$bindKey}"); 
    $this->param[$bindKey] = trim(substr($val, 1));
  }

  public function makeConstraintsSQL($constraints)
  {
    if (isset($constraints['order']))
      array_push($this->sql, " ORDER BY {$constraints['order']}");

    if (isset($constraints['limit']))
      array_push($this->sql, " LIMIT {$constraints['limit']}");

    if (isset($constraints['offset']))
      array_push($this->sql, " OFFSET {$constraints['offset']}");
  }

  public function getParam()
  {
    return $this->param;
  }

  public function unsetProparties()
  {
    $this->param    = array();
    $this->keyArray = array();
    $this->set      = false;
  }
  
  protected function setWhereQuery($query)
  {
    if ($this->set) {
      array_push($this->sql, ' AND ' . $query);
    } else {
      array_push($this->sql, ' WHERE ' . $query);
      $this->set = true;
    }
  }
}

class Sabel_Edo_Driver_PdoStatement
{
  private static $stmt;
  private static $sql;
  private static $keys = array();
  private static $constraints = array();
  public static $count = 0;
  public static $count1 = 0;

  public static function statement_exists($sql, $conditions, $constraints = null)
  {
    $result = true;
    if (!empty($conditions))
      $keys = array_keys($conditions);
    
    if (self::$sql         != $sql  || 
        self::$keys        != $keys || 
        self::$constraints != $constraints) { 

      self::$sql         = $sql;
      self::$keys        = $keys;
      self::$constraints = $constraints;
      $result = false;
    }        

    return $result;
  }

  public static function addStatement($stmt)
  {
    self::$stmt = $stmt;
  }

  public static function getStatement()
  {
    return self::$stmt;
  }
}
