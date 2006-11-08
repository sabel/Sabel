<?php

/**
 * Sabel_DB_Statement
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Statement
{
  protected
    $db  = '',
    $set = false,
    $sql = array();

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

  public function __construct($db, $escMethod = '')
  {
    $this->db        = $db;
    $this->escMethod = $escMethod;
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

  private function makeEitherQuery($condArray)
  {
    $conds = array();
    foreach ($condArray as $cond) $conds[] = $cond;

    $this->either = true;
    $this->makeConditionQuery($conds);
    $this->sql[] = ')';
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

  protected function getKey($condition)
  {
    return ($condition->not) ? 'NOT ' . $condition->key : $condition->key;
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
    $this->sql[] = $prefix . $query;
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

    if (!$escape) {
      $this->makeLikeSQL($val, $condition);
      return null;
    }

    if ($this->db === 'mssql') {
      $val = str_replace(array('%', '_'), array('[%]', '[_]'), $val);
      $this->makeLikeSQL($val, $condition);
    } else {
      $escapeChars = ':ZQXJKVBWYGFPMUzqxjkvbwygfpmu';

      for ($i = 0; $i < 30; $i++) {
        $esc = $escapeChars[$i];
        if (strpbrk($val, $esc) === false) {
          $val = str_replace(array('%', '_'), array("{$esc}%", "{$esc}_"), $val);
          $this->makeLikeSQL($val, $condition, $esc);
          break;
        }
      }
    }
  }

  public function escape($val)
  {
    $escMethod = $this->escMethod;

    if (is_string($val)) {
      $val = ($this->stripFlag) ? stripslashes($val) : $val;
      $val = ($escMethod === '') ? $val : $escMethod($val);
    } else if (is_bool($val)) {
      if (in_array($this->db, array('pgsql', 'mssql', 'sqlite'))) {
        $val = ($val) ? 'true' : 'false';
      } else if (in_array($this->db, array('mysql', 'firebird'))) {
        $val = ($val) ? 1 : 0;
      }
    }
    return $val;
  }
}
