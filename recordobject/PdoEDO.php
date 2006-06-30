<?php

require_once('EDO.php');
require_once('SQLObject.php');

class PdoEDO implements EDO
{
  private $pdo, $stmt, $sqlObj;

  private $preparedSQL;
  private $param = array();
  private $data  = array();

  public function __construct($pdo)
  {
    $this->pdo    = $pdo;
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
        $sign = substr($key, 0, 2);

        if ($val[0] == '>' || $val[0] == '<') {
          $this->sqlObj->makeLess_GreaterSQL($key, $val);
        } elseif ($sign == EDO::IN) {
          $key = str_replace($sign, '', $key);
          $this->sqlObj->makeWhereInSQL(trim($key), $val);
        } elseif ($sign == EDO::BET) {
          $key = str_replace($sign, '', $key);
          $this->sqlObj->makeBetweenSQL(trim($key), $val);
        } elseif ($sign == EDO::EITHER) {
          $key = str_replace($sign, '', $key);
          $this->sqlObj->makeEitherSQL(trim($key), $val);
        } elseif ($sign == EDO::LIKE) {
          $key = str_replace($sign, '', $key);
          $this->sqlObj->makeLikeSQL(trim($key), $val);
        } elseif (strtolower($val) == 'null') {
          $this->sqlObj->makeIsNullSQL($key);
        } elseif (strtolower($val) == 'not null') {
          $this->sqlObj->makeIsNotNullSQL($key);
        } else {
          $this->sqlObj->makeNormalConditionSQL($key, $val);
        }
      }
      $conditions = array();
    }

    if (!empty($constraints)) {
      $this->sqlObj->makeConstraintsSQL($constraints);
      $constraints = array();
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
    //echo $sql.'<br>';
    $this->makeBindParam();
    //var_dump($this->param);
    //exit;
    
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

    $this->sqlObj->unsetProparties();
  }
}

?>
