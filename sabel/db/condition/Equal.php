<?php

/**
 * Sabel_DB_Condition_Equal
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Equal extends Sabel_DB_Abstract_Condition
{
  protected $type = Sabel_DB_Condition::EQUAL;
  
  public function build(Sabel_DB_Abstract_Statement $stmt, &$counter)
  {
    $num = ++$counter;
    $stmt->setBindValue("param{$num}", $this->value);
    return $this->conditionColumn($stmt) . " = @param{$num}@";
  }
}
