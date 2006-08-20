<?php

/**
 * db driver for PDO
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Query_Bind extends Sabel_DB_Query_Factory
                          implements Sabel_DB_Query_Interface
{
  private $sql = array();
  private $set = null;

  private $count = 1;
  private $param = array();

  public function getSQL()
  {
    if (is_array($this->sql)) return join('', $this->sql);
  }

  public function setBasicSQL($sql)
  {
    $this->sql = array($sql);
  }

  public function makeNormalConditionSQL($key, $val)
  {
    $bindKey = $key . $this->count++;
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
    $this->setWhereQuery($key . ' IN (' . join(',', $val) . ')');
  }

  public function makeLikeSQL($key, $val)
  {
    $bindKey = $key . $this->count++;
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
    $bindKey  = $key . $this->count++;
    $bindKey2 = $key . $this->count++;

    $val1 = $val[0];
    $val2 = $val[1];

    $query = '(';
    if ($val1[0] === '<' || $val1[0] === '>') {
      $query .= "{$key} ${val1[0]} :{$bindKey}";
      $this->param[$bindKey] = trim(substr($val1, 1));
    } else if ($val1 === 'null') {
      $query .= "{$key} IS NULL";
    } else {
      $query .= "{$key}=:{$bindKey}";
      $this->param[$bindKey] = $val1;
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
    $bindKey  = $key . $this->count++;
    $this->setWhereQuery("{$key} {$val[0]} :{$bindKey}");
    $this->param[$bindKey] = trim(substr($val, 1));
  }

  public function makeConstraintSQL($constraints)
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
    $this->param = array();
    $this->count = 1;
    $this->set   = false;
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
