<?php

require_once('SQL.php');

class PdoSQL 
{
  private $sql, $set;

  public $keyArray = array();
  public $param    = array();

  public function getSQL()
  {
    return $this->sql;
  }

  public function setBasicSQL($sql)
  {
    $this->sql = $sql;
  }

  protected function checkKeyExists($key)
  {
    if (!array_key_exists($key, $this->keyArray)) {
      $this->keyArray[$key]['count'] = 2;
      return $key.'2';
    } else {
      $count = $this->keyArray[$key]['count'];
      $count = $count + 1;
      $this->keyArray[$key]['count'] = $count;
      return $key.$count;
    }
  }

  public function makeNormalConditionSQL($key, $val)
  {
    $bindKey = $this->checkKeyExists($key);

    if (!$this->set) {
      $this->sql .= " WHERE {$key}=:{$bindKey}";
    } else {
      $this->sql .= " AND {$key}=:{$bindKey}";
    }
    $this->set = true;
    $this->param["{$bindKey}"] = $val;
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

  public function makeWhereInSQL($key, $val)
  {
    if (!$this->set) {
      $this->sql .= " WHERE {$key} IN (". implode(',', $val) .")";
    } else {
      $this->sql .= " AND {$key} IN (". implode(',', $val) .")";
    }
    $this->set = true;
  }

  public function makeLikeSQL($key, $val)
  {
    $bindKey = $this->checkKeyExists($key);

    if (!$this->set) {
      $this->sql .= " WHERE {$key} LIKE :{$bindKey}";
    } else {
      $this->sql .= " AND {$key} LIKE :{$bindKey}";
    }
    $this->set = true;

    $this->param["{$bindKey}"] = $val;
  }

  public function makeBetweenSQL($key, $val)
  {
    if (!$this->set) {
      $this->sql .= " WHERE {$key} BETWEEN :from AND :to";
    } else {
      $this->sql .= " AND {$key} BETWEEN :from AND :to";
    }
    $this->set = true;

    $this->param["from"] = $val[0];
    $this->param["to"]   = $val[1];
  }

  public function makeEitherSQL($key, $val)
  {
    $bindKey  = $this->checkKeyExists($key);
    $bindKey2 = $this->checkKeyExists($key);

    $val1 = $val[0];
    $val2 = $val[1];

    if (!$this->set) {
      $str = " WHERE";
    } else {
      $str = " AND";
    }

    if ($val1[0] == '<' || $val1[0] == '>') {
      $this->sql .= $str." ({$key} {$val1[0]} :{$bindKey} OR";
      $val1 = trim(str_replace($val1[0], '', $val1));
    } else {
      $this->sql .= $str." ({$key}=:{$bindKey} OR";
    }
    if ($val2[0] == '<' || $val2[0] == '>') {
      $this->sql .= " {$key} {$val2[0]} :{$bindKey2})";
      $val2 = trim(str_replace($val2[0], '', $val2));
    } else {
      $this->sql .= " {$key}=:{$bindKey2})";
    }

    $this->set = true;

    $this->param["{$bindKey}"]  = $val1;
    $this->param["{$bindKey2}"] = $val2;
  }

  public function makeLess_GreaterSQL($key, $val)
  {
    $bindKey  = $this->checkKeyExists($key);

    if (!$this->set) {
      $this->sql .= " WHERE {$key} {$val[0]} :{$bindKey}";
    } else {
      $this->sql .= " AND {$key} {$val[0]} :{$bindKey}";
    }

    $val = str_replace($val[0], '', $val);
    $this->param["{$bindKey}"] = trim($val);

    $this->set = true;
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

  public function unsetProparties()
  {
    $this->param    = array();
    $this->keyArray = array();
    $this->set      = false;
  }
}

?>
