<?php

/**
 * Sabel_Db_Condition_In
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Condition_In extends Sabel_Db_Abstract_Condition
{
  protected $type = Sabel_Db_Condition::IN;
  
  public function build(Sabel_Db_Statement $stmt)
  {
    $column = $this->getQuotedColumn($stmt);
    if ($this->isNot) $column = "NOT " . $column;
    
    $prepared = array();
    foreach ($this->value as $v) {
      $n = ++self::$counter;
      $stmt->bind("__h{$n}", $v);
      $prepared[] = "@__h{$n}@";
    }
    
    return $column . " IN (" . implode(", ", $prepared) . ")";
  }
}
