<?php

require_once('SQL.php');

class PdoSQL 
{
  private $sql, $set;
  public $param = array();

  public function getSQL()
  {
    return $this->sql;
  }

  public function setBasicSQL($sql)
  {
    $this->sql = $sql;
  }

  public function makeNormalConditionSQL($key, $val)
  {
    if (!$this->set) {
      $this->sql .= " WHERE {$key}=:{$key}2";
    } else {
      $this->sql .= " AND {$key}=:{$key}2";
    }
    $this->set = true;

    $this->param["{$key}2"] = $val;
    unset($this->param[$key]);
  }

  public function makeIsNullSQL($key)
  {
    if (!$this->set) {
      $this->sql .= " WHERE {$key} IS NULL";
    } else {
      $this->sql .= " AND {$key} IS NULL";
    }
    $this->set = true;
  }

  public function makeIsNotNullSQL($key)
  {
    if (!$this->set) {
      $this->sql .= " WHERE {$key} IS NOT NULL";
    } else {
      $this->sql .= " AND {$key} IS NOT NULL";
    }
    $this->set = true;
  }

  public function makeBetweenSQL($key, $val, $sep)
  {
    if (!$this->set) {
      $this->sql .= " WHERE {$key} BETWEEN :from AND :to";
    } else {
      $this->sql .= " AND {$key} BETWEEN :from AND :to";
    }
    $this->set = true;

    $between = explode($sep, $val);
    $this->param["from"] = trim($between[0]);
    $this->param["to"]   = trim($between[1]);

    unset($this->param[$key]);
  }
        
  public function &makeEitherSQL($key, $val, $sep)
  {
    if (!$this->set) {
      $this->sql .= " WHERE ({$key}=:{$key}2 OR {$key}=:{$key}3)";
    } else {
      $this->sql .= " AND ({$key}=:{$key}2 OR {$key}=:{$key}3)";
    }
    $this->set = true;

    $values = explode($sep, $val);
    $this->param["{$key}2"] = trim($values[0]);
    $this->param["{$key}3"] = trim($values[1]);

    unset($this->param[$key]);
  }

  public function makeLess_GreaterSQL($key, $val, $sep)
  {
    if (strpos($val, $sep)) {
      $array = explode($sep, $val);
      $val1  = trim($array[0]);
      $val2  = trim($array[1]);
      if (!$this->set) {
        $this->sql .= " WHERE ({$key} {$val1[0]} :{$key}2 OR {$key} {$val2[0]} :{$key}3)";
      } else {
        $this->sql .= " AND ({$key} {$val1[0]} :{$key}2 OR {$key} {$val2[0]} :{$key}3)";
      }
      $val1  = str_replace($val1[0], '', $val1);
      $val2  = str_replace($val2[0], '', $val2);
      $this->param["{$key}2"] = trim($val1);
      $this->param["{$key}3"] = trim($val2);
    } else {
      if (!$this->set) {
        $this->sql .= " WHERE {$key} {$val[0]} :{$key}2";
      } else {
        $this->sql .= " AND {$key} {$val[0]} :{$key}2";
      }
      $val = str_replace($val[0], '', $val);
      $this->param["{$key}2"] = trim($val);
    }
    $this->set = true;

    unset($this->param[$key]);
  }

  public function makeConstraintsSQL($constraints)
  {
    if (!is_null($constraints['order']))
      $this->sql .= " ORDER BY {$constraints['order']}";

    if (!is_null($constraints['limit']))
      $this->sql .= " LIMIT {$constraints['limit']}";

    if (!is_null($constraints['offset']))
      $this->sql .= " OFFSET {$constraints['offset']}";
  }
}

?>
