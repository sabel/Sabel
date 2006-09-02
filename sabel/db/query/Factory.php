<?php

class Sabel_DB_Query_Factory
{
  protected
    $escMethod = '',
    $dbName    = '',
    $stripFlag = false,
    $set       = false;

  public function __construct($dbName, $methodName = null)
  {
    $this->dbName    = $dbName;
    $this->escMethod = $methodName;
    $this->stripFlag = (defined('SABEL')) ? false : get_magic_quotes_gpc();
  }

  public function makeConditionQuery($conditions)
  {
    if (!$conditions) return true;

    $nmlCount = 0;
    foreach ($conditions as $key => $val) {
      if ($val[0] == '>' || $val[0] == '<') {
        $this->makeLess_GreaterSQL($key, $val);
      } else if (strstr($key, Sabel_DB_Driver_Interface::IN)) {
        $key = str_replace(Sabel_DB_Driver_Interface::IN, '', $key);
        $this->makeWhereInSQL($key, $val);
      } else if (strstr($key, Sabel_DB_Driver_Interface::BET)) {
        $key = str_replace(Sabel_DB_Driver_Interface::BET, '', $key);
        $this->makeBetweenSQL($key, $val);
      } else if (strstr($key, Sabel_DB_Driver_Interface::EITHER)) {
        $key = str_replace(Sabel_DB_Driver_Interface::EITHER, '', $key);
        $this->prepareEitherSQL($key, $val);
      } else if (strstr($key, Sabel_DB_Driver_Interface::LIKE)) {
        $key = str_replace(Sabel_DB_Driver_Interface::LIKE, '', $key);
        $this->prepareLikeSQL($key, $val);
      } else if (strtolower($val) === 'null') {
        $this->makeIsNullSQL($key);
      } else if (strtolower($val) === 'not null') {
        $this->makeIsNotNullSQL($key);
      } else {
        $this->makeNormalSQL($key, $val);
        $nmlCount++;
      }
    }
    return (count($conditions) === $nmlCount);
  }

  public function makeConstraintQuery($constraints)
  {
    if (isset($constraints['group']))
      array_push($this->sql, " GROUP BY {$constraints['group']}");

    if (isset($constraints['order']))
      array_push($this->sql, " ORDER BY {$constraints['order']}");

    if (isset($constraints['limit'])) {
      if ($this->dbName === 'firebird') {
        $tmp    = substr(join('', $this->sql), 6);
        $query  = "FIRST {$constraints['limit']} ";
        $query .= (isset($constraints['offset'])) ? "SKIP {$constraints['offset']}" : 'SKIP 0';

        $this->sql = array('SELECT ' . $query . $tmp);
      } else {
        array_push($this->sql, " LIMIT {$constraints['limit']}");
      }
    }

    if (isset($constraints['offset']))
      array_push($this->sql, " OFFSET {$constraints['offset']}");
  }

  public function getSQL()
  {
    return join('', $this->sql);
  }

  public function setBasicSQL($sql)
  {
    $this->sql = array($sql);
  }

  public function makeIsNullSQL($key)
  {
    $this->setWhereQuery($key . ' IS NULL');
  }

  public function makeIsNotNullSQL($key)
  {
    $this->setWhereQuery($key . ' IS NOT NULL');
  }

  public function setWhereQuery($query)
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
    $search_str = ':ZQXJKVBWYGFPMUzqxjkvbwygfpmu';

    if (is_array($val)) {
      $escape = $val[1];
      $val    = $val[0];
    } else {
      $escape = true;
    }

    if (strpbrk($val, '_') !== false && $escape) {
      for ($i = 0; $i < 30; $i++) {
        $esc = $search_str[$i];
        if (strpbrk($val, $esc) === false) {
          $val = str_replace('_', "{$esc}_", $val);
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
      $condition[] = $val[0];
      $condition[] = $val[1];
    } else {
      $keys = array();
      for ($i = 0; $i < count($val); $i++) $keys[] = $key;
      $condition[] = $keys;
      $condition[] = $val;
    }

    $count = count($condition[0]);
    if ($count !== count($condition[1]))
      throw new Exception('Query_Factory::prepareEitherSQL() make column same as number of values.');

    $query  = '(';

    for ($i = 0; $i < $count; $i++) {
      $key    = $condition[0][$i];
      $query .= $this->makeEitherSQL($key, $condition[1][$i]);
      if (($i + 1) !== $count) $query .= ' OR ';
    }

    $query .= ')';
    $this->setWhereQuery($query);
  }
}
