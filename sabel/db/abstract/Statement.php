<?php

/**
 * Sabel_DB_Abstract_Statement
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Statement
{
  protected $sql = "";

  abstract public function create(Sabel_DB_Model_Executer $executer);
  abstract public function getStatementType();

  public function setSql($sql)
  {
    $this->sql = $sql;

    return $this;
  }

  public function getSql()
  {
    return $this->sql;
  }
}
