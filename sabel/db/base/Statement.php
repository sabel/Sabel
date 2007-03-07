<?php

/**
 * Sabel_DB_Base_Statement
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @subpackage base
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Base_Statement
{
  protected
    $db  = '',
    $set = false,
    $sql = array();

  private
    $escMethod = '';

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

      if ($condition->force) {
        $this->makeForceSQL($condition); 
        continue;
      }

      switch ($condition->type) {
        case Sabel_DB_Condition::NORMAL:
          $this->makeNormalSQL($condition);
          break;
        case Sabel_DB_Condition::BET:
          $this->makeBetweenSQL($condition);
          break;
        case Sabel_DB_Condition::LIKE:
          $this->prepareLikeSQL($condition);
          break;
        case Sabel_DB_Condition::IN:
          $this->makeWhereInSQL($condition);
          break;
        case Sabel_DB_Condition::ISNULL:
          $this->makeIsNullSQL($condition->key);
          break;
        case Sabel_DB_Condition::NOTNULL:
          $this->makeIsNotNullSQL($condition->key);
          break;
        case Sabel_DB_Condition::COMP:
          $this->makeCompareSQL($condition);
          break;
      }
    }
  }

  private function makeEitherQuery($condArray)
  {
    $this->either = true;
    $this->makeConditionQuery($condArray);
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
    $val = $condition->value;

    $values = array();
    foreach ($val as $v) $values[] = $this->escape($v);
    $this->setWhereQuery($this->getKey($condition) . ' IN (' . join(',', $values) . ')');
  }

  protected function makeForceSQL($condition)
  {
    $val = $condition->value;
    if (!is_array($val)) $val = (array)$val;

    $values = array();
    foreach ($val as &$v) $values[] = $this->escape($v);

    $cond = vsprintf($condition->key, $values);
    $this->setWhereQuery($cond);
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
    } else {
      list($val, $esc) = $this->escapeLikeSQL($val);
      $this->makeLikeSQL($val, $condition, $esc);
    }
  }

  protected function escapeLikeSQL($val)
  {
    $escapeChars = 'ZQXJKVBWYGFPMUCDzqxjkvbwygfpmu';

    for ($i = 0; $i < 30; $i++) {
      $esc = $escapeChars{$i};
      if (strpbrk($val, $esc) === false) {
        $val = preg_replace('/([%_])/', $esc . '$1', $val);
        return array($val, $esc);
      }
    }
  }

  protected function escape($val)
  {
    $escMethod = $this->escMethod;

    if ($val === __TRUE__ || $val === __FALSE__) {
      switch ($this->db) {
        case 'pgsql':
        case 'mssql':
        case 'sqlite':
          $val = ($val === __TRUE__) ? 'true' : 'false';
          break;
        case 'mysql':
        case 'firebird':
          $val = ($val === __TRUE__) ? 1 : 0;
          break;
      }
    } elseif (is_string($val)) {
      $val = ($escMethod === '') ? $val : $escMethod($val);
    }
    return $val;
  }
}
