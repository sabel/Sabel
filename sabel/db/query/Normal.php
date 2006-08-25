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
  protected
    $sql    = array(),
    $set    = false,
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
    if ($key !== '')
      $val = $this->toArrayEitherCondition($key, $val);

    $c = count($val[0]);
    if ($c !== count($val[1]))
      throw new Exception('Query_Normal::makeEitherSQL() make column same as number of values.');

    $query  = '(';
    for ($i = 0; $i < $c; $i++) {
      $key = $val[0][$i];
      $this->_makeEitherSQL($key, $val[1][$i], $query);
      if (($i + 1) !== $c) $query .= ' OR ';
    }
    $query .= ')';
    $this->setWhereQuery($query);
  }

  protected function _makeEitherSQL($key, $val, &$query)
  {
    if ($val[0] === '<' || $val[0] === '>') {
      $value    = $this->escape(trim(substr($val, 1)));
      $query .= "{$key} {$val[0]} '{$value}'";
    } else if (strtolower($val) === 'null') {
      $query .= "{$key} IS NULL";
    } else {
      $query .= "{$key}='". $this->escape($val) ."'";
    }
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

  protected function escape($val)
  {
    return $this->driver->escape($val);
  }
}
