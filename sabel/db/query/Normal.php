<?php

/**
 * Query Maker for Native
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Query_Normal extends Sabel_DB_Query_Factory implements Sabel_DB_Query_Interface
{
  protected $sql = array();

  public function makeNormalSQL($key, $val)
  {
    $this->setWhereQuery($this->_getNormalSQL($key, $val));
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

  public function unsetProparties()
  {
    $this->set = false;
  }

  protected function escape($val)
  {
    return $this->driver->escape($val);
  }
}
