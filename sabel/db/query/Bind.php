<?php

/**
 * Query Maker for Prepared
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Query_Bind extends Sabel_DB_Query_Factory
                          implements Sabel_DB_Query_Interface
{
  protected
    $sql    = array(),
    $set    = false,
    $driver = null;

  private $count = 1;
  private $param = array();

  public function __construct($driver)
  {
    $this->driver = $driver;
  }

  public function makeNormalSQL($key, $val)
  {
    $this->setWhereQuery($this->_getNormalSQL($key, $val, $key.$this->count++));
  }

  public function makeWhereInSQL($key, $val)
  {
    $this->setWhereQuery($key . ' IN (' . join(',', $val) . ')');
  }

  public function makeLikeSQL($key, $val, $esc = null)
  {
    $bindKey = $key . $this->count++;
    $query   = "{$key} LIKE :{$bindKey}";
    if (isset($esc)) $query .= " escape '{$esc}'";

    $this->setWhereQuery($query);
    $this->param[$bindKey] = $val;
  }

  public function makeBetweenSQL($key, $val)
  {
    $this->setWhereQuery("{$key} BETWEEN :from AND :to");
    $this->param["from"] = $val[0];
    $this->param["to"]   = $val[1];
  }

  public function makeEitherSQL($key, $val)
  {
    return $this->_makeEitherSQL($key, $val, $key.$this->count++);
  }

  protected function _makeEitherSQL($key, $val, $bindKey)
  {
    if ($val[0] === '<' || $val[0] === '>') {
      return $this->_getLess_GreaterSQL($key, $val, $bindKey);
    } else if (strtolower($val) === 'null') {
      return "{$key} IS NULL";
    } else {
      return $this->_getNormalSQL($key, $val, $bindKey);
    }
  }

  public function makeLess_GreaterSQL($key, $val)
  {
    $this->setWhereQuery($this->_getLess_GreaterSQL($key, $val, $key.$this->count++));
  }

  protected function _getLess_GreaterSQL($key, $val, $bindKey)
  {
    $query = "{$key} {$val[0]} :{$bindKey}";
    $this->param[$bindKey] = trim(substr($val, 1));
    return $query;
  }

  protected function _getNormalSQL($key, $val, $bindKey)
  {
    $query = "{$key}=:{$bindKey}";
    $this->param[$bindKey] = $val;
    return $query;
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
