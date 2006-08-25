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

  public function makeNormalSQL($key, $val)
  {
    $this->setWhereQuery("{$key}='". $this->escape($val) ."'");
  }

  public function makeWhereInSQL($key, $val)
  {
    foreach ($val as $v) $this->escape($v);
    $this->setWhereQuery($key . ' IN (' . join(',', $val) . ')');
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
    return $this->_makeEitherSQL($key, $val);
  }

  protected function _makeEitherSQL($key, $val)
  {
    if ($val[0] === '<' || $val[0] === '>') {
      $value  = $this->escape(trim(substr($val, 1)));
      return "{$key} {$val[0]} '{$value}'";
    } else if (strtolower($val) === 'null') {
      return "{$key} IS NULL";
    } else {
      return "{$key}='". $this->escape($val) ."'";
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
