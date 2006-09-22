<?php

/**
 * Query Maker for Native
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Native_Query extends Sabel_DB_Driver_Query
{
  public function makeUpdateSQL($table, $data)
  {
    $sql = array();
    foreach ($data as $key => $val) {
      $val = $this->escape($val);
      array_push($sql, "{$key}='{$val}'");
    }
    $this->setBasicSQL("UPDATE {$table} SET " . join(',', $sql));
  }

  public function makeInsertSQL($table, $data)
  {
    $columns = array();
    $values  = array();

    foreach ($data as $key => $val) {
      array_push($columns, $key);
      $val = $this->escape($val);
      array_push($values, "'{$val}'");
    }

    $sql = array("INSERT INTO {$table}(");
    array_push($sql, join(',', $columns));
    array_push($sql, ") VALUES(");
    array_push($sql, join(',', $values));
    array_push($sql, ')');

    return join('', $sql);
  }

  public function makeNormalSQL($key, $val)
  {
    $this->setWhereQuery($this->_getNormalSQL($key, $val));
  }

  public function makeLikeSQL($key, $val, $esc = null)
  {
    $query = $key . " LIKE '" . $this->escape($val) . "'";
    if (isset($esc)) $query .= " escape '{$esc}'";

    $this->setWhereQuery($query);
  }

  public function makeBetweenSQL($key, $val)
  {
    $val1 = $this->escape($val[0]);
    $val2 = $this->escape($val[1]);
    $this->setWhereQuery("{$key} BETWEEN '{$val1}' AND '{$val2}'");
  }

  public function makeEitherSQL($key, $val)
  {
    if ($val[0] === '<' || $val[0] === '>') {
      return $this->_makeLess_GreaterSQL($key, $val);
    } else if (strtolower($val) === 'null') {
      return "{$key} IS NULL";
    } else {
      return $this->_getNormalSQL($key, $val);
    }
  }

  public function makeLess_GreaterSQL($key, $val)
  {
    $this->setWhereQuery($this->_makeLess_GreaterSQL($key, $val));
  }

  protected function _makeLess_GreaterSQL($key, $val)
  {
    $lg   = substr($val, 0, strpos($val, ' '));
    $val1 = $this->escape(trim(substr($val, strlen($lg))));
    return "{$key} {$lg} '{$val1}'";
  }

  protected function _getNormalSQL($key, $val)
  {
    return "{$key}='{$this->escape($val)}'";
  }

  public function unsetProperties()
  {
    $this->set = false;
  }
}
