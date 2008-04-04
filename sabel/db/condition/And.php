<?php

/**
 * Sabel_DB_Condition_And
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_And extends Sabel_Object
{
  protected $conditions = array();
  
  public function add(Sabel_DB_Abstract_Condition $condition)
  {
    $this->conditions[] = $condition;
  }
  
  public function build(Sabel_DB_Statement $sql)
  {
    $query = array();
    foreach ($this->conditions as $condition) {
      $query[] = $condition->build($sql);
    }
    
    return "(" . implode(" AND ", $query) . ")";
  }
}
