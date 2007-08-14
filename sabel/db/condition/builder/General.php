<?php

/**
 * Sabel_DB_Condition_Builder_General
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Builder_General
  extends    Sabel_DB_Abstract_ConditionBuilder
  implements Sabel_DB_Condition_Builder_Interface
{
  public function initialize($driver)
  {
    $this->driver = $driver;
  }

  public function buildNormal($condition)
  {
    $val = $this->driver->escape($condition->value);
    return $this->getKey($condition) . " = " . $val;
  }

  public function buildBetween($condition)
  {
    list ($from, $to) = $this->driver->escape($condition->value);
    return $this->getKey($condition) . " BETWEEN $from AND $to";
  }

  public function buildCompare($condition)
  {
    list ($lg, $val) = $condition->value;
    return $condition->key . " $lg " . $this->driver->escape($val);
  }

  protected function createLike($val, $condition, $esc = null)
  {
    $val = $this->driver->escape($val);
    $sql = $this->getKey($condition) . " LIKE " . $val;
    if (isset($esc)) $sql .= " escape '{$esc}'";

    return $sql;
  }
}
