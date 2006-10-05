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

  public function makeConstraintQuery($constraints)
  {
    if (isset($constraints['group']))
      array_push($this->sql, " GROUP BY {$constraints['group']}");

    $order = (isset($constraints['order'])) ? $constraints['order'] : null;
    if ($order) array_push($this->sql, " ORDER BY {$constraints['order']}");

    $limit  = (isset($constraints['limit'])) ? $constraints['limit'] : null;
    $offset = (isset($constraints['offset'])) ? $constraints['offset'] : null;
    $column = (isset($constraints['defColumn'])) ? $constraints['defColumn'] : null;

    $paginate = new Sabel_DB_Driver_Native_Paginate($this->sql, $limit, $offset);

    if ($this->dbName === 'firebird') {
      $this->sql = $paginate->firebirdPaginate();
    } else if ($this->dbName === 'mssql') {
      $this->sql = $paginate->mssqlPaginate($column, $order);
    } else {
      $this->sql = $paginate->standardPaginate();
    }
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
    $this->setWhereQuery("{$key} BETWEEN '{$val[0]}' AND '{$val[1]}'");
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
