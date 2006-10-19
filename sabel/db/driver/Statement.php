<?php

abstract class Sabel_DB_Driver_Statement
{
  protected
    $dbName   = '',
    $set      = false,
    $sql      = array();

  private
    $escMethod = '',
    $stripFlag = false;

  private
    $either = false,
    $eCount = 1;

  public abstract function unsetProperties();

  protected abstract function makeUpdateSQL($table, $data);
  protected abstract function makeInsertSQL($table, $data);

  protected abstract function makeNormalSQL($condition);
  protected abstract function makeBetweenSQL($condition);
  protected abstract function makeCompareSQL($condition);
  protected abstract function makeLikeSQL($val, $condition, $esc = null);

  public function __construct($dbName, $methodName = null)
  {
    $this->dbName    = $dbName;
    $this->escMethod = $methodName;
    $this->stripFlag = (defined('SABEL')) ? false : get_magic_quotes_gpc();
  }

  public function makeConditionQuery($conditions)
  {
    if (!$conditions) return true;

    foreach ($conditions as $key => $condition) {
      if (is_array($condition)) {
        $this->makeEitherQuery($condition);
        unset($conditions[$key]);
        continue;
      }

      switch ($condition->type) {
        case Sabel_DB_Condition::NORMAL:
          $this->makeNormalSQL($condition);
          continue;
        case Sabel_DB_Condition::BET:
          $this->makeBetweenSQL($condition);
          continue;
        case Sabel_DB_Condition::LIKE:
          $this->prepareLikeSQL($condition);
          continue;
        case Sabel_DB_Condition::IN:
          $this->makeWhereInSQL($condition);
          continue;
        case Sabel_DB_Condition::ISNULL:
          $this->makeIsNullSQL($condition->key);
          continue;
        case Sabel_DB_Condition::NOTNULL:
          $this->makeIsNotNullSQL($condition->key);
          continue;
        case Sabel_DB_Condition::COMP:
          $this->makeCompareSQL($condition);
          continue;
      }
    }
  }

  protected function makeEitherQuery($condArray)
  {
    $conds = array();
    foreach ($condArray as $cond) $conds[] = $cond;

    $this->either = true;
    $this->makeConditionQuery($conds);
    array_push($this->sql, ')');
    $this->eCount = 1;
    $this->either = false;
  }

  public function getSQL()
  {
    return join('', $this->sql);
  }

  public function setBasicSQL($sql)
  {
    $this->sql = array($sql);
  }

  protected function makeIsNullSQL($key)
  {
    $this->setWhereQuery($key . ' IS NULL');
  }

  protected function makeIsNotNullSQL($key)
  {
    $this->setWhereQuery($key . ' IS NOT NULL');
  }

  protected function makeWhereInSQL($condition)
  {
    $values = array();
    foreach ($condition->value as $val) $values[] = $this->escape($val);
    $this->setWhereQuery($this->getKey($condition) . " IN (" . join(',', $values) . ')');
  }

  protected function setWhereQuery($query)
  {
    if ($this->set) {
      if ($this->either) {
        $prefix = ($this->eCount === 1) ? ' AND (' : ' OR ';
      } else {
        $prefix = ' AND ';
      }
    } else {
      $prefix = ($this->either) ? ' WHERE (' : ' WHERE ';
      $this->set = true;
    }
    if ($this->either) $this->eCount++;
    array_push($this->sql, $prefix . $query);
  }

  protected function prepareLikeSQL($condition)
  {
    if (is_array($condition->value)) {
      $escape = $condition->value[1];
      $val    = $condition->value[0];
    } else {
      $escape = true;
      $val    = $condition->value;
    }

    $exist = (strpbrk($val, '_') !== false || strpbrk($val, '%') !== false);

    if ($exist && $escape && $this->dbName === 'mssql') {
      $val = str_replace(array('%', '_'), array('[%]', '[_]'), $val);
      $this->makeLikeSQL($val, $condition);
    } else if ($exist && $escape) {
      $escapeString = ':ZQXJKVBWYGFPMUzqxjkvbwygfpmu';
      for ($i = 0; $i < 30; $i++) {
        $esc = $escapeString[$i];
        if (strpbrk($val, $esc) === false) {
          $val = str_replace(array('%', '_'), array("{$esc}%", "{$esc}_"), $val);
          $this->makeLikeSQL($val, $condition, $esc);
          break;
        }
      }
    } else {
      $this->makeLikeSQL($val, $condition);
    }
  }

  protected function getKey($condition)
  {
    return ($condition->not) ? 'NOT ' . $condition->key : $condition->key;
  }

  public function escape($val)
  {
    $escMethod = $this->escMethod;

    if (is_string($val)) {
      $val = ($this->stripFlag) ? stripslashes($val) : $val;
      $val = (is_null($escMethod)) ? $val : $escMethod($val);
    } else if (is_bool($val)) {
      $db = $this->dbName;
      if ($db === 'pgsql' || $db === 'mssql' || $db === 'sqlite') {
        $val = ($val) ? 'true' : 'false';
      } else if ($db === 'mysql' || $db === 'firebird') {
        $val = ($val) ? 1 : 0;
      }
    }
    return $val;
  }
}
