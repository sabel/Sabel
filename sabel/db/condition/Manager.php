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
class Sabel_DB_Condition_Manager extends Sabel_Object
{
  protected $conditions = array();

  public function add($condition)
  {
    if ($this->isObject($condition)) {
      $this->conditions[$condition->column()] = $condition;
    } else {
      $this->conditions[] = $condition;
    }
  }

  public function has($column)
  {
    return isset($this->conditions[$column]);
  }

  public function getConditions()
  {
    return $this->conditions;
  }

  public function create($key, $val = null)
  {
    if (empty($key)) return;

    if (is_array($key)) {
      foreach ($key as $column => $value) {
        $this->add(Sabel_DB_Condition::create(Sabel_DB_Condition::EQUAL, $column, $value));
      }
    } else {
      $this->add(Sabel_DB_Condition::create(Sabel_DB_Condition::EQUAL, $key, $val));
    }
  }

  public function isObject($condition)
  {
    return ($condition instanceof Sabel_DB_Abstract_Condition);
  }

  public function isEmpty()
  {
    return empty($this->conditions);
  }

  public function clear()
  {
    $conditions = $this->conditions;
    $this->conditions = array();

    return $conditions;
  }

  public function build(Sabel_DB_Abstract_Sql $sql)
  {
    $set     = false;
    $counter = 0;
    $query   = array();

    foreach ($this->conditions as $condition) {
      $query[] = ($set) ? " AND " : " WHERE ";
      $query[] = $condition->build($sql, $counter);
      $set = true;
    }

    return implode("", $query);
  }
}
