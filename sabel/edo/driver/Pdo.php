<?php

uses('sabel.edo.driver.Interface');
uses('sabel.edo.SQLObject');

class Sabel_Edo_Driver_Pdo implements Sabel_Edo_Driver_Interface
{
  private $pdo, $stmt, $sqlObj, $myDb;

  private $param = array();
  private $data  = array();
  private $keys  = array();

  private $childTable = '';
  private $childCondition = array();


  public function __construct($conn, $myDb)
  {
    $this->pdo    = $conn;
    $this->myDb   = $myDb;
    $this->sqlObj = new PdoSQL();
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
      } else {
        array_push($sql, ",{$key}=:{$key}");
      }
      $set = true;
    }

    $this->sqlObj->setBasicSQL(implode('', $sql));
  }

  public function setInsertSQL($table, $data)
  {
  
  }

  public function setAggregateSQL($table, $idColumn, $functions)
  {
    $sql = array("SELECT {$idColumn}");

    foreach ($functions as $key => $val)
      array_push($sql, ", {$key}({$val}) AS {$key}_{$val}");

    array_push($sql, " FROM {$table} GROUP BY {$idColumn}");
    $this->sqlObj->setBasicSQL(implode('', $sql));
  }

  public function executeInsert($table, $data, $id_exists = null)
  {
    if (!$id_exists && $this->myDb == 'pgsql')
      $data['id'] = $this->getNextNumber($table);

    $this->data = $data;

    if ($table == 'order_line') $this->disp = true;

    $sql = array("INSERT INTO {$table}(");
    $set = false;

    foreach ($data as $key => $val) {
      if (!$set) {
        array_push($sql, "{$key}");
      } else {
        array_push($sql, ",{$key}");
      }
      $set = true;
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

    $this->stmtFlag = Sabel_Edo_Driver_Statement::statement_exists(implode('', $sql), $data);

    if (!$this->stmtFlag)
      $this->sqlObj->setBasicSQL(implode('', $sql));

    return $this->execute();
  }

  public function getLastInsertId()
  {
    if ($this->myDb == 'pgsql') {
      return $this->lastInsertId;
    } elseif ($this->myDb == 'mysql') {
      $this->execute('SELECT last_insert_id()');
      $row = $this->fetch(Sabel_Edo_Driver_Interface::FETCH_ASSOC);
      return $row['last_insert_id()'];
    } else {
      return 'todo else';
    }
  }

  private function getNextNumber($table)
  {
    if ($this->myDb == 'pgsql') {
      $this->execute('SELECT nextval(\''.$table.'_id_seq\');');
      $row = $this->fetch();
      if (($this->lastInsertId = (int)$row[0]) == 0) {
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
    $this->stmtFlag = Sabel_Edo_Driver_Statement::statement_exists($this->sqlObj->getSQL(), $conditions, $constraints);

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
    try {
      if (!is_null($sql)) {
        $this->stmt = $this->pdo->prepare($sql);
      } elseif ($this->stmtFlag) {
        $this->stmt = Sabel_Edo_Driver_Statement::getStatement();
      } elseif (is_null($sql) && is_null($this->sqlObj->getSQL())) {
        throw new Exception('Error: None SQL-Query!! execute EDO::makeQuery() beforehand');
      } else {
        $sql = $this->sqlObj->getSQL();
        if ($this->stmt = $this->pdo->prepare($sql)) {
          Sabel_Edo_Driver_Statement::addStatement($this->stmt);
        } else {
          throw new PDOException('Error: PDOStatement is null.');
        }
      }
    } catch (Exception $e) {
      print_r($e->getMessage()."\n");
      print_r($e->getTrace());
    } catch (PDOException $pe) {
      print_r($pe->getMessage()."\n");
      print_r($sql);
      print_r($this->pdo->errorInfo());
    }

    $this->makeBindParam();

    try {
      if (empty($this->param)) {
        $result = $this->stmt->execute();
      } else {
        $result = $this->stmt->execute($this->param);
        $tmp = $this->param;
        $this->param = array();
      }

      if (!$result)
        throw new PDOException('Error: PDOStatement::execute()');

      return $result;
    } catch (PDOException $pe) {
      print_r($pe->getMessage()."\n");
      print_r($this->stmt);
      print_r($tmp);
      print_r($this->stmt->errorInfo());
    }
  }

  public function fetch($style = null)
  {
    if ($style == Sabel_Edo_Driver_Interface::FETCH_ASSOC) {
      $result = $this->stmt->fetch(PDO::FETCH_ASSOC, $cursor, $offset);
    } else {
      $result = $this->stmt->fetch(PDO::FETCH_BOTH, $cursor, $offset);
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

class Sabel_Edo_Driver_Statement
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

?>
