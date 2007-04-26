<?php

/**
 * Sabel_DB_Condition_Manager
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Manager
{
  protected $conditions       = array();
  protected $uniqueConditions = array();

  public function add($condition)
  {
    if ($condition instanceof Sabel_DB_Condition_Object) {
      $this->conditions[$condition->key] = $condition;
    } else {
      $this->conditions[] = $condition;
    }
  }

  public function getConditions()
  {
    return $this->conditions;
  }

  public function create($arg1, $arg2, $arg3 = null)
  {
    if (empty($arg1)) return;

    if (is_array($arg1)) {
      foreach ($arg1 as $key => $val) {
        $this->add(new Sabel_DB_Condition_Object($key, $val));
      }
    } else {
      $this->add(new Sabel_DB_Condition_Object($arg1, $arg2, $arg3));
    }
  }

  public function isObject($condition)
  {
    return ($condition instanceof Sabel_DB_Condition_Object);
  }

  public function addUnique($condition)
  {
    if ($this->isObject($condition)) {
      $this->uniqueConditions[$condition->key] = $condition;
      return $this->uniqueConditions;
    } else {
      return false;
    }
  }

  public function getUniqueConditions()
  {
    return $this->uniqueConditions;
  }

  public function isEmpty()
  {
    return (empty($this->conditions));
  }

  public function clear()
  {
    $this->conditions = array();
  }

  public function build($driver)
  {
    $builder = $driver->getConditionBuilder();

    $set   = false;
    $query = array();

    foreach ($this->conditions as $condition) {
      $query[] = ($set) ? " AND " : " WHERE ";
      $query[] = $condition->build($builder);
      $set = true;
    }

    return implode("", $query);
  }
}