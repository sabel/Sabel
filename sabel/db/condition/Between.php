<?php

/**
 * Sabel_DB_Condition_Between
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Between extends Sabel_DB_Abstract_Condition
{
  public function build(Sabel_DB_Abstract_Sql $sql, &$counter)
  {
    $f    = ++$counter;
    $t    = ++$counter;
    $val  = $this->value;
    $fkey = $sql->setBindValue("param{$f}", $val[0]);
    $tkey = $sql->setBindValue("param{$t}", $val[1]);
    
    return $this->getColumnWithNot() . " BETWEEN $fkey AND $tkey";
  }
}
