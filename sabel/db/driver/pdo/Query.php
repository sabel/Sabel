<?php

/**
 * Query Maker for Pdo
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Pdo_Query extends Sabel_DB_Driver_Statement
{
  private $count = 1;
  private $param = array();

  public function makeUpdateSQL($table, $data)
  {
    $sql = array();
    foreach (array_keys($data) as $key) $sql[] = "{$key}=:{$key}";
    $this->setBasicSQL("UPDATE $table SET " . join(',', $sql));

    foreach ($data as $key => $val) $data[$key] = $this->escape($val);
    return $data;
  }

  public function makeInsertSQL($table, $data)
  {
    $columns = array();
    $values  = array();

    foreach ($data as $key => $val) {
      $columns[] = $key;
      $values[]  = ':' . $key;
    }

    $sql = array("INSERT INTO $table (");
    $sql[] = join(',', $columns);
    $sql[] = ") VALUES(";
    $sql[] = join(',', $values);
    $sql[] = ')';

    foreach ($data as $key => $val) $data[$key] = $this->escape($val);
    return array(join('', $sql), $data);
  }

  public function makeConstraintQuery($const)
  {
    $sql =& $this->sql;
    if (isset($const['group']))  $sql[] = ' GROUP BY ' . $const['group'];
    if (isset($const['having'])) $sql[] = ' HAVING '   . $const['having'];
    if (isset($const['order']))  $sql[] = ' ORDER BY ' . $const['order'];
    if (isset($const['limit']))  $sql[] = ' LIMIT '    . $const['limit'];
    if (isset($const['offset'])) $sql[] = ' OFFSET '   . $const['offset'];
  }

  protected function makeNormalSQL($condition)
  {
    $bindKey = $condition->key . $this->count++;
    $this->param[$bindKey] = $this->escape($condition->value);
    $this->setWhereQuery($this->getKey($condition) . "=:{$bindKey}");
  }

  protected function makeLikeSQL($val, $condition, $esc = null)
  {
    $bindKey = $condition->key . $this->count++;
    $query   = $this->getKey($condition) . " LIKE :{$bindKey}";
    if (isset($esc)) $query .= " escape '{$esc}'";

    $this->setWhereQuery($query);
    $this->param[$bindKey] = $this->escape($val);
  }

  protected function makeBetweenSQL($condition)
  {
    $f   = $this->count++;
    $t   = $this->count++;
    $val = $condition->value;

    $this->setWhereQuery($this->getKey($condition) . " BETWEEN :from{$f} AND :to{$t}");
    $this->param["from{$f}"] = $val[0];
    $this->param["to{$t}"]   = $val[1];
  }

  protected function makeCompareSQL($condition)
  {
    $bindKey = $condition->key . $this->count++;

    $lg = $condition->value[0];
    $this->param[$bindKey] = $this->escape($condition->value[1]);
    $this->setWhereQuery($condition->key . " $lg :{$bindKey}");
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
