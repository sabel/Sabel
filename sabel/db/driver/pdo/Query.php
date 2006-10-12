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
    $this->setBasicSQL("UPDATE $table SET " . join(',', $sql));

    foreach ($data as $key => $val) $data[$key] = $this->escape($val);
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

    $sql = array("INSERT INTO $table (");
    array_push($sql, join(',', $columns));
    array_push($sql, ") VALUES(");
    array_push($sql, join(',', $values));
    array_push($sql, ')');

    foreach ($data as $key => $val) $data[$key] = $this->escape($val);
    return array(join('', $sql), $data);
  }

  public function makeConstraintQuery($const)
  {
    if (isset($const['group']))  array_push($this->sql, ' GROUP BY ' . $const['group']);
    if (isset($const['order']))  array_push($this->sql, ' ORDER BY ' . $const['order']);
    if (isset($const['limit']))  array_push($this->sql, ' LIMIT '    . $const['limit']);
    if (isset($const['offset'])) array_push($this->sql, ' OFFSET '   . $const['offset']);
  }

  protected function makeNormalSQL($key, $val)
  {
    $this->setWhereQuery($this->getNormalSQL($key, $val, $key . $this->count++));
  }

  protected function makeLikeSQL($key, $val, $esc = null)
  {
    $bindKey = $key . $this->count++;
    $query   = "$key LIKE :{$bindKey}";
    if (isset($esc)) $query .= " escape '{$esc}'";

    $this->setWhereQuery($query);
    $this->param[$bindKey] = $this->escape($val);
  }

  protected function makeBetweenSQL($key, $val)
  {
    $f = $this->count++;
    $t = $this->count++;

    $this->setWhereQuery("$key BETWEEN :from{$f} AND :to{$t}");
    $this->param["from{$f}"] = $val[0];
    $this->param["to{$t}"]   = $val[1];
  }

  protected function makeEitherSQL($key, $val)
  {
    if ($val[0] === '<' || $val[0] === '>') {
      return $this->getLessGreaterSQL($key, $val, $key . $this->count++);
    } else if (strtolower($val) === 'null') {
      return "$key IS NULL";
    } else {
      return $this->getNormalSQL($key, $val, $key . $this->count++);
    }
  }

  protected function makeLessGreaterSQL($key, $val)
  {
    $this->setWhereQuery($this->getLessGreaterSQL($key, $val, $key.$this->count++));
  }

  protected function getLessGreaterSQL($key, $val, $bindKey)
  {
    list($lg, $val) = array_map('trim', explode(' ', $val));
    $this->param[$bindKey] = $this->escape($val);
    return "$key $lg :{$bindKey}";
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
