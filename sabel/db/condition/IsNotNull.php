<?php

/**
 * Sabel_DB_Condition_IsNotNull
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_IsNotNull extends Sabel_DB_Abstract_Condition
{
  protected $type = Sabel_DB_Condition::ISNOTNULL;
  
  public function build(Sabel_DB_Abstract_Statement $stmt, &$counter)
  {
    return $stmt->quoteIdentifier($this->column) . " IS NOT NULL";
  }
}
