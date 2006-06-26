<?php

require_once('EDO.php');

class PdoEDO implements EDO
{
  private $pdo, $stmt, $sqlObj;

  private $preparedSQL;
  private $param = array();
  private $data  = array();

  public function __construct($site)
  {
    $dsn  = 'pgsql:host='.$site['database'].';dbname='.$site['db_name'];
    $user = $site['auth_user'];
    $pass = $site['auth_pass'];
    
    $this->pdo = new PDO($dsn, $user, $pass);
    $this->sqlObj = new PdoSQL();
  }

  public function setBasicSQL($sql)
  {
    $this->sqlObj->setBasicSQL($sql);
  }

  public function setUpdateSQL($table, $data)
  {
    $this->data = $data;

    $sql = "UPDATE {$table} SET";
    $set = false;

    foreach ($data as $key => $val) {
      if (!$set) {
        $sql .= " {$key}=:{$key}";
      } else {
        $sql .= ",{$key}=:{$key}";
      }
      $set = true;
    }

    $this->sqlObj->setBasicSQL($sql);
  }

  public function setInsertSQL($table, $data)
  {
    $this->data = $data;

    $sql = "INSERT INTO {$table}(";
    $set = false;

    foreach ($data as $key => $val) {
      if (!$set) {
        $sql .= "{$key}";
      } else {
        $sql .= ",{$key}";
      }
      $set = true;
    }

    $sql .= ") VALUES(";
    $set = false;

    foreach ($data as $key => $val) {
      if (!$set) {
        $sql .= ":{$key}";
      } else {
        $sql .= ",:{$key}";
      }
      $set = true;
    }

    $sql .= ');';

    $this->sqlObj->setBasicSQL($sql);
  }

  public function makeQuery(&$conditions = null, &$constraints = null)
  { 
    if (!empty($conditions)) {

      foreach ($conditions as $key => $val) {
        if ($val[0] == '>' || $val[0] == '<') {
          $this->sqlObj->makeLess_GreaterSQL($key, $val, EDO::EITHER_SEP);
        } elseif (strpos($val, EDO::BETWEEN_SEP)) {
          $this->sqlObj->makeBetweenSQL($key, $val, EDO::BETWEEN_SEP);
        } elseif (strpos($val, EDO::EITHER_SEP)) {
          $this->sqlObj->makeEitherSQL($key, $val, EDO::EITHER_SEP);
        } elseif (is_null($val)) {
          $this->sqlObj->makeIsNullSQL($key);
        } elseif (strtolower($val) == 'not null' || $val == '!') {
          $this->sqlObj->makeIsNotNullSQL($key);
        } else {
          $this->sqlObj->makeNormalConditionSQL($key, $val);
        }
      }
      unset($conditions);
    }

    if (!empty($constraints)) {
      $this->sqlObj->makeConstraintsSQL($constraints);
      unset($constraints);
    }
  }

  public function execute($_sql = null)
  {
    $sql = $_sql;

    if (is_null($sql)) {
      $sql = $this->sqlObj->getSQL();
      if (is_null($sql)) {
        echo 'Error: None SQL-Query!! execute EDO::makeQuery() beforehand';
        exit;
      } else {
        $this->preparedSQL = $sql;
      }
    }

    $this->stmt = $this->pdo->prepare($sql);
    $this->makeBindParam();

    if (empty($this->param)) {
      return $this->stmt->execute();
    } else {
      return $this->stmt->execute($this->param);
    }
  }

  public function fetch($style = null, $cursor = null, $offset = null)
  {
    if ($style == EDO::FETCH_ASSOC) {
      return $this->stmt->fetch(PDO::FETCH_ASSOC, $cursor, $offset);
    } else {
      return $this->stmt->fetch(PDO::FETCH_BOTH, $cursor, $offset);
    }
  }

  public function executePreparedSQL($data, $conditions = null)
  {
    //todo: execute $this->preparedSQL;
  }

  private function makeBindParam()
  {
    $this->param = $this->sqlObj->param;

    if (!empty($this->data))
      $this->param = array_merge($this->param, $this->data);

    if (!empty($this->param)) {
      foreach ($this->param as $key => $val) {
        if (is_null($val)) continue;
        
        $this->param[":{$key}"] = $val;
        unset($this->param["{$key}"]);
      }
    }

    unset($this->sqlObj->param);
  }
}

?>
