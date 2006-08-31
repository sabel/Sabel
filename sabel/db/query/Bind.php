<?php

/**
 * Query Maker for Prepared
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Query_Bind extends Sabel_DB_Query_Factory implements Sabel_DB_Query_Interface
{
  protected $sql = array();

  private $count = 1;
  private $param = array();

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
    if ($val[0] === '<' || $val[0] === '>') {
      return $this->_makeLess_GreaterSQL($key, $val, $key.$this->count++);
    } else if (strtolower($val) === 'null') {
      return "{$key} IS NULL";
    } else {
      return $this->_getNormalSQL($key, $val, $key.$this->count++);
    }
  }

  public function makeLess_GreaterSQL($key, $val)
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
