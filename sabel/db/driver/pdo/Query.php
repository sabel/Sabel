<?php

/**
 * Query Maker for Prepared
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Pdo_Query extends Sabel_DB_Driver_Query
{
  private $count = 1;
  private $param = array();

  public function makeUpdateSQL($table, $data)
  {
    $sql = array();
    foreach (array_keys($data) as $key) array_push($sql, "{$key}=:{$key}");
    $this->setBasicSQL("UPDATE {$table} SET " . join(',', $sql));

    foreach ($data as $key => $d) $data[$key] = $this->escape($d);
    return $data;
  }

  public function makeInsertSQL($table, $data)
  {
    $columns = array();
    $values  = array();

    foreach ($data as $key => $val) {
      array_push($columns, $key);
      array_push($values, ':' . $key);
    }

    $sql = array("INSERT INTO {$table}(");
    array_push($sql, join(',', $columns));
    array_push($sql, ") VALUES(");
    array_push($sql, join(',', $values));
    array_push($sql, ')');

    foreach ($data as $key => $val) $data[$key] = $this->escape($val);
    return array(join('', $sql), $data);
  }

  public function makeConstraintQuery($constraints)
  {
    if (isset($constraints['group']))
      array_push($this->sql, " GROUP BY {$constraints['group']}");

    if (isset($constraints['order']))
      array_push($this->sql, " ORDER BY {$constraints['order']}");

    if (isset($constraints['limit']))
      array_push($this->sql, " LIMIT {$constraints['limit']}");

    if (isset($constraints['offset']))
      array_push($this->sql, " OFFSET {$constraints['offset']}");
  }

  protected function makeNormalSQL($key, $val)
  {
    $this->setWhereQuery($this->getNormalSQL($key, $val, $key.$this->count++));
  }

  protected function makeLikeSQL($key, $val, $esc = null)
  {
    $bindKey = $key . $this->count++;
    $query   = "{$key} LIKE :{$bindKey}";
    if (isset($esc)) $query .= " escape '{$esc}'";

    $this->setWhereQuery($query);
    $this->param[$bindKey] = $this->escape($val);
  }

  protected function makeBetweenSQL($key, $val)
  {
    $f = $this->count++;
    $t = $this->count++;

    $this->setWhereQuery("{$key} BETWEEN :from{$f} AND :to{$t}");
    $this->param["from{$f}"] = $val[0];
    $this->param["to{$t}"]   = $val[1];
  }

  protected function makeEitherSQL($key, $val)
  {
    if ($val[0] === '<' || $val[0] === '>') {
      return $this->getLessGreaterSQL($key, $val, $key.$this->count++);
    } else if (strtolower($val) === 'null') {
      return "{$key} IS NULL";
    } else {
      return $this->getNormalSQL($key, $val, $key.$this->count++);
    }
  }

  protected function makeLessGreaterSQL($key, $val)
  {
    $this->setWhereQuery($this->getLessGreaterSQL($key, $val, $key.$this->count++));
  }

  protected function getLessGreaterSQL($key, $val, $bindKey)
  {
    $lg = substr($val, 0, strpos($val, ' '));
    $this->param[$bindKey] = trim(substr($val, strlen($lg)));
    return "{$key} {$lg} :{$bindKey}";
  }

  protected function getNormalSQL($key, $val, $bindKey)
  {
    $this->param[$bindKey] = $this->escape($val);
    return "{$key}=:{$bindKey}";
  }

  public function getParam()
  {
    return $this->param;
  }

  public function unsetProperties()
  {
    $this->param = array();
    $this->count = 1;
    $this->set   = false;
  }
}
