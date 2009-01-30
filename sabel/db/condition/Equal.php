<?php

/**
 * Sabel_Db_Condition_Equal
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Condition_Equal extends Sabel_Db_Abstract_Condition
{
  protected $type = Sabel_Db_Condition::EQUAL;
  
  public function build(Sabel_Db_Statement $stmt)
  {
    $num = ++self::$counter;
    $stmt->setBindValue("__h{$num}", $this->value);
    
    $column = $this->getQuotedColumn($stmt);
    if ($this->isNot) $column = "NOT " . $column;
    
    return $column . " = @__h{$num}@";
  }
}
