<?php

/**
 * Sabel_DB_Condition_LessEqual
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_LessEqual extends Sabel_DB_Abstract_Condition
{
  protected $type = Sabel_DB_Condition::LESS_EQUAL;
  
  public function build(Sabel_DB_Abstract_Statement $stmt, &$counter)
  {
    $num = ++$counter;
    $stmt->setBindValue("param{$num}", $this->value);
    return $stmt->quoteIdentifier($this->column) . " <= @param{$num}@";
  }
}
