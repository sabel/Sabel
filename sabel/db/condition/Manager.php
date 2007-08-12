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
  protected $conditions = array();

  public function add($condition)
  {
    if ($this->isObject($condition)) {
      $this->conditions[$condition->key] = $condition;
    } else {
      $this->conditions[] = $condition;
    }
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
        $this->add(new Sabel_DB_Condition_Object($column, $value));
      }
    } else {
      $this->add(new Sabel_DB_Condition_Object($key, $val));
    }
  }

  public function isObject($condition)
  {
    return ($condition instanceof Sabel_DB_Condition_Object);
  }

  public function isEmpty()
  {
    return (empty($this->conditions));
  }

  public function clear()
  {
    $conditions = $this->conditions;
    $this->conditions = array();

    return $conditions;
  }

  public function build(Sabel_DB_Abstract_Statement $stmt)
  {
    $builder = new Sabel_DB_Condition_Builder($stmt);

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
