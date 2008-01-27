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
    if ($this->isIndividualCondition($condition)) {
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
    if (is_array($key)) {
      foreach ($key as $column => $value) {
        $c = Sabel_DB_Condition::create(Sabel_DB_Condition::EQUAL, $column, $value);
        $this->conditions[$c->column()] = $c;
      }
    } else {
      $c = Sabel_DB_Condition::create(Sabel_DB_Condition::EQUAL, $key, $val);
      $this->conditions[$c->column()] = $c;
    }
  }
  
  public function isIndividualCondition($condition)
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
  
  public function build(Sabel_DB_Abstract_Statement $stmt)
  {
    $counter = 0;
    
    if (count($this->conditions) === 0) {
      return "";
    } else {
      $query = array();
      foreach ($this->conditions as $condition) {
        $query[] = $condition->build($stmt, $counter);
      }
      
      return "WHERE " . implode(" AND ", $query);
    }
  }
}
