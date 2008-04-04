<?php

/**
 * Sabel_DB_Condition_In
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_In extends Sabel_DB_Abstract_Condition
{
  protected $type = Sabel_DB_Condition::IN;
  
  public function build(Sabel_DB_Statement $stmt)
  {
    $column = $this->getQuotedColumn($stmt);
    if ($this->isNot) $column = "NOT " . $column;
    
    $prepared = array();
    foreach ($this->value as $v) {
      $n = ++self::$counter;
      $stmt->setBindValue("param{$n}", $v);
      $prepared[] = "@param{$n}@";
    }
    
    return $column . " IN (" . implode(", ", $prepared) . ")";
  }
}
