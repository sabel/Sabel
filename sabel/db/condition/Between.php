<?php

/**
 * Sabel_DB_Condition_Between
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Between extends Sabel_DB_Abstract_Condition
{
  protected $type = Sabel_DB_Condition::BETWEEN;
  
  public function build(Sabel_DB_Abstract_Statement $stmt)
  {
    $f   = ++self::$counter;
    $t   = ++self::$counter;
    $val = $this->value;
    
    $stmt->setBindValue("param{$f}", $val[0]);
    $stmt->setBindValue("param{$t}", $val[1]);
    
    $column = $this->getQuotedColumn($stmt);
    if ($this->isNot) $column = "NOT " . $column;
    
    return $column . " BETWEEN @param{$f}@ AND @param{$t}@";
  }
}
