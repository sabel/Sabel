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
    $sql = array(),
    $set = null;

  private $count = 1;
  private $param = array();

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
    $bindKey = $key . $this->count++;
    $this->setWhereQuery("{$key}=:{$bindKey}");
    $this->param[$bindKey] = $val;
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
    $this->setWhereQuery($key . ' IN (' . join(',', $val) . ')');
  }

  public function makeLikeSQL($key, $val)
  {
    $bindKey    = $key . $this->count++;
    $search_str = ';:ZQXJKVBWYGFPMUzqxjkvbwygfpmu';

    if (strpbrk($val, '_') !== false) {
      for ($i = 0; $i < 30; $i++) {
        $c = $search_str[$i];
        if (strpbrk($val, $c) === false) {
          $val = str_replace('_', "{$c}_", $val);
          $this->setWhereQuery("{$key} LIKE :{$bindKey} escape '{$c}'");
          $this->param[$bindKey] = $val;
          break;
        }
      }
    } else {
      $this->setWhereQuery("{$key} LIKE :{$bindKey}");
      $this->param[$bindKey] = $val;
    }
  }

  public function makeBetweenSQL($key, $val)
  {
    $this->setWhereQuery("{$key} BETWEEN :from AND :to");
    $this->param["from"] = $val[0];
    $this->param["to"]   = $val[1];
  }

  public function makeEitherSQL($key, $val)
  {
    if ($key !== '')
      $val = $this->toArrayEitherCondition($key, $val);

    $c = count($val[0]);
    if ($c !== count($val[1]))
      throw new Exception('Query_Bind::makeEitherSQL() make column same as number of values.');

    $query  = '(';
    for ($i = 0; $i < $c; $i++) {
      $key = $val[0][$i];
      $this->_makeEitherSQL($key, $val[1][$i], $query, $key.$this->count++);
      if (($i + 1) !== $c) $query .= ' OR ';
    }
    $query .= ')';
    $this->setWhereQuery($query);
  }

  protected function _makeEitherSQL($key, $val, &$query, $bindKey)
  {
    if ($val[0] === '<' || $val[0] === '>') {
      $query .= "{$key} {$val[0]} :{$bindKey}";
      $this->param[$bindKey] = trim(substr($val, 1));
    } else if (strtolower($val) === 'null') {
      $query .= "{$key} IS NULL";
    } else {
      $query .= "{$key}=:{$bindKey}";
      $this->param[$bindKey] = $val;
    }
  }

  public function makeLess_GreaterSQL($key, $val)
  {
    $bindKey  = $key . $this->count++;
    $this->setWhereQuery("{$key} {$val[0]} :{$bindKey}");
    $this->param[$bindKey] = trim(substr($val, 1));
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
