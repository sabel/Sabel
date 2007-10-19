<?php

/**
 * Sabel_DB_Condition_Or
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Or extends Sabel_Object
{
  protected $conditions = array();

  public function add($condition)
  {
    $this->conditions[] = $condition;
  }

  public function build($builder)
  {
    $conditions = $this->conditions;

    $query = array();
    foreach ($conditions as $condition) {
      $query[] = $condition->build($builder);
    }

    $query = implode(" OR ", $query);
    return "( " . $query . " )";
  }
}
