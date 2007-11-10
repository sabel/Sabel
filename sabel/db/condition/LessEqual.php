<?php

/**
 * Sabel_DB_Condition_LessEqual
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_LessEqual extends Sabel_DB_Abstract_Condition
{
  public function build(Sabel_DB_Abstract_Sql $sql, &$counter)
  {
    $bindKey = $sql->setBindValue("param" . ++$counter, $this->value);
    return $this->column . " <= $bindKey";
  }
}
