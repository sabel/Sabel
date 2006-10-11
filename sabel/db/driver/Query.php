<?php

abstract class Sabel_DB_Driver_Query
{
  protected
    $escMethod = '',
    $dbName    = '',
    $nmlCount  = 0,
    $sql       = array(),
    $stripFlag = false,
    $set       = false;

  protected abstract function makeUpdateSQL($table, $data);
  protected abstract function makeInsertSQL($table, $data);
  protected abstract function makeNormalSQL($key, $val);
  protected abstract function makeLikeSQL($key, $val, $esc = null);
  protected abstract function makeBetweenSQL($key, $val);
  protected abstract function makeEitherSQL($key, $val);
  protected abstract function makeLessGreaterSQL($key, $val);
  public    abstract function unsetProperties();


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
      switch ($condition->type) :
        case Sabel_DB_Condition::NORMAL:
          $this->makeNormalConditionQuery($key, $condition);
          continue;
        case Sabel_DB_Condition::ISNULL:
          $this->makeIsNullSQL($key);
          continue;
        case Sabel_DB_Condition::NOTNULL:
          $this->makeIsNotNullSQL($key);
          continue;
        case Sabel_DB_Condition::LIKE:
          $this->prepareLikeSQL($key, $condition->value);
          continue;
        case Sabel_DB_Condition::IN:
          $this->makeWhereInSQL($key, $condition->value);
          continue;
        case Sabel_DB_Condition::BET:
          $this->makeBetweenSQL($key, $condition->value);
          continue;
        case Sabel_DB_Condition::EITHER:
          $this->prepareEitherSQL($key, $condition->value);
          continue;
      endswitch;
    }
    return (count($conditions) === $this->nmlCount);
  }

  protected function makeNormalConditionQuery($key, $condition)
  {
    $value = $condition->value;

    if ($value[0] === '>' || $value[0] === '<') {
      $this->makeLessGreaterSQL($key, $value);
    } else {
      $this->makeNormalSQL($key, $value);
      $this->nmlCount++;
    }
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

  protected function makeWhereInSQL($key, $val)
  {
    $values = array();
    foreach ($val as $v) $values[] = $this->escape($v);
    $this->setWhereQuery($key . ' IN (' . join(',', $values) . ')');
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

  protected function prepareLikeSQL($key, $val)
  {
    if (is_array($val)) {
      $escape = $val[1];
      $val    = $val[0];
    } else {
      $escape = true;
    }

    $exist = (strpbrk($val, '_') !== false || strpbrk($val, '%') !== false);

    if ($exist && $escape && $this->dbName === 'mssql') {
      $this->makeLikeSQL($key, str_replace(array('%', '_'), array('[%]', '[_]'), $val));
    } else if ($exist && $escape) {
      $escapeString = ':ZQXJKVBWYGFPMUzqxjkvbwygfpmu';
      for ($i = 0; $i < 30; $i++) {
        $esc = $escapeString[$i];
        if (strpbrk($val, $esc) === false) {
          $val = str_replace(array('%', '_'), array("{$esc}%", "{$esc}_"), $val);
          $this->makeLikeSQL($key, $val, $esc);
          break;
        }
      }
    } else {
      $this->makeLikeSQL($key, $val);
    }
  }

  protected function prepareEitherSQL($key, $val)
  {
    $condition = array();

    if ($key === '') {
      list($keys, $val) = array_values($val);
    } else {
      $keys = array();
      for ($i = 0; $i < count($val); $i++) $keys[] = $key;
    }
    $condition['key'] = $keys;
    $condition['val'] = $val;

    if (($count = count($condition['key'])) !== count($condition['val']))
      throw new Exception('Error: make keys same as number of values.');

    $query  = '(';
    for ($i = 0; $i < $count; $i++) {
      $key    = $condition['key'][$i];
      $query .= $this->makeEitherSQL($key, $condition['val'][$i]);
      if (($i + 1) !== $count) $query .= ' OR ';
    }

    $query .= ')';
    $this->setWhereQuery($query);
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
