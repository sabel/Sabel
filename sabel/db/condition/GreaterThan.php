<?php

/**
 * Sabel_DB_Condition_GreaterThan
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_GreaterThan extends Sabel_DB_Abstract_Condition
{
  protected $type = Sabel_DB_Condition::GREATER_THAN;
  
  public function build(Sabel_DB_Statement $stmt)
  {
    $num = ++self::$counter;
    $stmt->setBindValue("param{$num}", $this->value);
    
    $column = $this->getQuotedColumn($stmt);
    if ($this->isNot) $column = "NOT " . $column;
    
    return $column . " > @param{$num}@";
  }
}
