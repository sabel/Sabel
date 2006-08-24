<?php

/**
 * Query Maker for Native
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Query_Normal extends Sabel_DB_Query_Factory
                            implements Sabel_DB_Query_Interface
{
  protected $sql = array();

  private
    $set    = null,
    $driver = null;

  public function __construct($driver)
  {
    $this->driver = $driver;
  }

  public function getSQL()
  {
    return join('', $this->sql);
  }

  public function setBasicSQL($sql)
  {
    $this->sql = array($sql);
  }

  public function makeNormalConditionSQL($key, $val)
  {
    $this->setWhereQuery("{$key}='". $this->escape($val) ."'");
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
    foreach ($val as $v) $this->escape($v);
    $this->setWhereQuery($key . ' IN (' . join(',', $val) . ')');
  }

  public function makeLikeSQL($key, $val)
  {
    $val = $this->escape(str_replace('_', '\_', $val));
    $this->setWhereQuery("{$key} LIKE '{$val}'");
  }

  public function makeBetweenSQL($key, $val)
  {
    $val1 = $this->escape($val[0]);
    $val2 = $this->escape($val[1]);
    $this->setWhereQuery("{$key} BETWEEN '{$val1}' AND '{$val2}'");
  }

  public function makeEitherSQL($key, $val)
  {
    $val1 = $val[0];
    $val2 = $val[1];

    $query = '(';
    if ($val1[0] === '<' || $val1[0] === '>') {
      $val    = $this->escape(trim(substr($val1, 1)));
      $query .= "{$key} {$val1[0]} '{$val}'";
    } else if (strtolower($val1) === 'null') {
      $query .= "{$key} IS NULL";
    } else {
      $query .= "{$key}='". $this->escape($val1) ."'";
    }

    $query .= ' OR ';

    if ($val2[0] === '<' || $val2[0] === '>') {
      $val    = $this->escape(trim(substr($val2, 1)));
      $query .= "{$key} {$val2[0]} '{$val}'";
    } else if ($val2 === 'null') {
      $query .= "{$key} IS NULL";
    } else {
      $query .= "{$key}='". $this->escape($val2) ."'";
    }

    $query .= ')';
    $this->setWhereQuery($query);
  }

  public function makeLess_GreaterSQL($key, $val)
  {
    $val1 = $this->escape(trim(substr($val, 1)));
    $this->setWhereQuery("{$key} {$val[0]} '{$val1}'");
  }

  public function unsetProparties()
  {
    $this->set = false;
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

  protected function escape($val)
  {
    return $this->driver->escape($val);
  }
}
