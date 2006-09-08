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
    return "UPDATE {$table} SET " . join(',', $sql);
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

    return join('', $sql);
  }

  protected function makeNormalSQL($key, $val)
  {
    $this->setWhereQuery($this->_getNormalSQL($key, $val, $key.$this->count++));
  }

  protected function makeWhereInSQL($key, $val)
  {
    $this->setWhereQuery($key . ' IN (' . join(',', $val) . ')');
  }

  protected function makeLikeSQL($key, $val, $esc = null)
  {
    $bindKey = $key . $this->count++;
    $query   = "{$key} LIKE :{$bindKey}";
    if (isset($esc)) $query .= " escape '{$esc}'";

    $this->setWhereQuery($query);
    $this->param[$bindKey] = $val;
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
      return $this->_makeLess_GreaterSQL($key, $val, $key.$this->count++);
    } else if (strtolower($val) === 'null') {
      return "{$key} IS NULL";
    } else {
      return $this->_getNormalSQL($key, $val, $key.$this->count++);
    }
  }

  protected function makeLess_GreaterSQL($key, $val)
  {
    $this->setWhereQuery($this->_makeLess_GreaterSQL($key, $val, $key.$this->count++));
  }

  protected function _makeLess_GreaterSQL($key, $val, $bindKey)
  {
    $lg = substr($val, 0, strpos($val, ' '));
    $this->param[$bindKey] = trim(substr($val, strlen($lg)));
    return "{$key} {$lg} :{$bindKey}";
  }

  protected function _getNormalSQL($key, $val, $bindKey)
  {
    $this->param[$bindKey] = $val;
    return "{$key}=:{$bindKey}";
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
}
