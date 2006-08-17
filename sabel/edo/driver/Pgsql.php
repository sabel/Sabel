<?php

//uses('sabel.edo.driver.Interface');
//uses('sabel.edo.query.php');

class Sabel_Edo_Driver_Pgsql implements Sabel_Edo_Driver_Interface
{
  private $conn, $stmt, $sqlObj;

  private $param = array();
  private $data  = array();

  public function __construct($conn)
  {
    $this->conn   = $conn;
    $this->sqlObj = new PgsqlQuery();
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
    $count = 1;

    foreach ($data as $key => $val) {
      if (!$set) {
        array_push($sql, " {$key}=\${$count}");
      } else {
        array_push($sql, ",{$key}=\${$count}");
      }
      $set = true;
      $count++;
    }

    $this->sqlObj->setBasicSQL(implode('', $sql));
    $this->sqlObj->setCount($count);
  }

  public function setAggregateSQL($table, $idColumn, $functions)
  {
    $sql = array("SELECT {$idColumn}");

    foreach ($functions as $key => $val)
      array_push($sql, ", {$key}({$val}) AS {$key}_{$val}");

    array_push($sql, " FROM {$table} GROUP BY {$idColumn}");
    $this->sqlObj->setBasicSQL(implode('', $sql));
  }

  public function executeInsert($table, $data, $defColumn)
  {
    //if (is_null($data[$defColumn]))
    //  $data[$defColumn] = $this->getNextNumber($table);

    $this->data = $data;

    $sql   = array("INSERT INTO {$table}(");
    $set   = false;

    foreach ($data as $key => $val) {
      if (!$set) {
        array_push($sql, "{$key}");
      } else {
        array_push($sql, ",{$key}");
      }
      $set = true;
    }

    array_push($sql, ') VALUES(');
    $set = false;
    $count = 1;

    foreach ($data as $key => $val) {
      if (!$set) {
        array_push($sql, "\${$count}");
      } else {
        array_push($sql, ",\${$count}");
      }
      $set = true;
      $count++;
    }
    array_push($sql, ');');

    $this->stmtFlag = Sabel_Edo_Driver_PgsqlStatement::statement_exists(implode('', $sql), $data);

    if (!$this->stmtFlag)
      $this->sqlObj->setBasicSQL(implode('', $sql));

    return $this->execute();
  }

  public function getLastInsertId()
  {
    return $this->lastInsertId;
  }

  private function getNextNumber($table)
  {
    $this->execute('SELECT nextval(\''.$table.'_id_seq\');');
    $row = $this->fetch();
    if (($this->lastInsertId =(int) $row[0]) === 0) {
      //throw new Exception($table.'_id_seq is not found.');
    } else {
      return $this->lastInsertId;
    }
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $this->stmtFlag = Sabel_Edo_Driver_PgsqlStatement::statement_exists($this->sqlObj->getSQL(), $conditions, $constraints);

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

  public function execute($sql = null, $param = null)
  {
    try {
      if (!is_null($sql)) {
        if (!($this->result = pg_query($this->conn, $sql))) {
          throw new Exception('Error: Edo_Driver_Pgsql::pg_query()');
        } else {
          return true;
        }
      } elseif ($this->stmtFlag) {
        $this->stmt = Sabel_Edo_Driver_PgsqlStatement::getStatement();
      } elseif (is_null($sql) && is_null($this->sqlObj->getSQL())) {
        throw new Exception('Error: None SQL-Query!! execute EDO::makeQuery() beforehand');
      } else {
        $sql = $this->sqlObj->getSQL();
        if ($this->stmt = pg_prepare($this->conn, '', $sql)) {
          Sabel_Edo_Driver_PgsqlStatement::addStatement($this->stmt);
        } else {
          throw new Exception('Error: PgsqlStatement is null.');
        }
      }
    } catch (Exception $e) {
      print_r($e->getMessage()."\n");
      print_r($sql);
    }

    $this->makeBindParam();

    try {
      if (!($this->result = pg_execute($this->conn, '', $this->param))) {
        throw new Exception('Error: Sabel_Edo_Driver_Pgsql::execute()');
        return false;
      } else {
        $tmp = $this->param;
        $this->param = array();
        return true;
      }
    } catch (Exception $e) {
      print_r($e->getMessage()."\n");
      print_r($this->param);
      //print_r($e->getTrace());
    }
  }

  public function fetch($style = null)
  {
    if ($style == Sabel_Edo_Driver_Interface::FETCH_ASSOC) {
      return pg_fetch_assoc($this->result);
    } else {
      return pg_fetch_array($this->result);
    }
  }

  public function fetchAll($style = null)
  {
    return pg_fetch_all($this->result);
  }

  private function makeBindParam()
  {
    $this->param = $this->sqlObj->getParam();

    if (!empty($this->param) && !empty($this->data)) {
      $this->param = $this->data + $this->param;
    } else {
      if (!empty($this->data)) $this->param = $this->data;
    }

    $this->data  = array();
    $this->sqlObj->unsetProparties();
  }
}

class PgsqlQuery 
{
  private $sql, $set;
  private $param = array();
  private $count = 1;

  public function setCount($count)
  {
    $this->count = $count;
  }

  public function getSQL()
  {
    if (is_array($this->sql))
      return implode('', $this->sql);
  }

  public function setBasicSQL($sql)
  {
    $this->sql = array($sql);
  }

  public function makeNormalConditionSQL($key, $val)
  {
    if (!$this->set) {
      array_push($this->sql, " WHERE {$key}=\${$this->count}");
    } else {
      array_push($this->sql, " AND {$key}=\${$this->count}");
    }
    $this->set = true;
    $this->count++;
    $this->param[] = $val;
  }

  public function makeIsNullSQL($key)
  {
    if (!$this->set) {
      array_push($this->sql, " WHERE {$key} IS NULL");
    } else {
      array_push($this->sql, " AND {$key} IS NULL");
    }
    $this->set = true;
  }

  public function makeIsNotNullSQL($key)
  {
    if (!$this->set) {
      array_push($this->sql, " WHERE {$key} IS NOT NULL");
    } else {
      array_push($this->sql, " AND {$key} IS NOT NULL");
    }
    $this->set = true;
  }

  public function makeWhereInSQL($key, $val)
  {
    if (!$this->set) {
      array_push($this->sql, " WHERE {$key} IN (". implode(',', $val) .")");
    } else {
      array_push($this->sql, " AND {$key} IN (". implode(',', $val) .")");
    }
    $this->set = true;
  }

  public function makeLikeSQL($key, $val)
  {
    if (!$this->set) {
      array_push($this->sql, " WHERE {$key} LIKE \${$this->count}");
    } else {
      array_push($this->sql, " AND {$key} LIKE \${$this->count}");
    }
    $this->set = true;
    $this->count++;

    $val = str_replace('_', '\_', $val);
    $this->param[] = $val;
  }

  public function makeBetweenSQL($key, $val)
  {
    $to = $this->count + 1;

    if (!$this->set) {
      array_push($this->sql, " WHERE {$key} BETWEEN \${$this->count} AND \${$to}");
    } else {
      array_push($this->sql, " AND {$key} BETWEEN \${$this->count} AND \${$to}");
    }
    $this->set = true;
    $this->count += 2;

    $this->param[] = $val[0];
    $this->param[] = $val[1];
  }

  public function makeEitherSQL($key, $val)
  {
    $val1 = $val[0];
    $val2 = $val[1];

    if (!$this->set) {
      $str = " WHERE";
    } else {
      $str = " AND";
    }

    if ($val1[0] == '<' || $val1[0] == '>') {
      array_push($this->sql, $str." ({$key} {$val1[0]} \${$this->count} OR");
      $val1 = trim(substr_replace($val1, '', 0, 1));
      $this->param[] = $val1;
    } elseif ($val1 == 'null') {
      array_push($this->sql, $str." ({$key} IS NULL OR");
    } else {
      array_push($this->sql, $str." ({$key}=\${$this->count} OR");
      $this->param[] = $val1;
    }

    $c2 = $this->count + 1;
    if ($val2[0] == '<' || $val2[0] == '>') {
      array_push($this->sql, " {$key} {$val2[0]} \${$c2})");
      $val2 = trim(substr_replace($val2, '', 0, 1));
      $this->param[] = $val2;
    } elseif ($val2 == 'null') {
      array_push($this->sql, $str." {$key} IS NULL)");
    } else {
      array_push($this->sql, " {$key}=\${$c2})");
      $this->param[] = $val2;
    }
    $this->set = true;
    $this->count += 2;
  }

  public function makeLess_GreaterSQL($key, $val)
  {
    if (!$this->set) {
      array_push($this->sql, " WHERE {$key} {$val[0]} \${$this->count}");
    } else {
      array_push($this->sql, " AND {$key} {$val[0]} \${$this->count}");
    }
    $this->set = true;
    $this->count++;

    $val = substr_replace($val, '', 0, 1);
    $this->param[] = trim($val);
  }

  public function makeConstraintsSQL($constraints)
  {
    if (!is_null($constraints['order']))
      array_push($this->sql, " ORDER BY {$constraints['order']}");

    if (!is_null($constraints['limit']))
      array_push($this->sql, " LIMIT {$constraints['limit']}");

    if (!is_null($constraints['offset']))
      array_push($this->sql, " OFFSET {$constraints['offset']}");
  }

  public function getParam()
  {
    return $this->param;
  }

  public function unsetProparties()
  {
    $this->param = array();
    $this->set   = false;
    $this->count = 1;
  }
}

class Sabel_Edo_Driver_PgsqlStatement
{
  private static $stmt;
  private static $sql;
  private static $keys = array();
  private static $constraints = array();

  public static function statement_exists($sql, $conditions, $constraints = null)
  {
    $result = true;
    if (!empty($conditions))
      $keys = array_keys($conditions);
    
    if (self::$sql         !=  $sql  || 
        self::$keys        !== $keys || 
        self::$constraints !=  $constraints) { 

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
