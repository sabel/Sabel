<?php

/**
 * Sabel_DB_Sql_Statement_Update
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Statement_Update extends Sabel_DB_Abstract_Statement
{
  public function getStatementType()
  {
    return Sabel_DB_Statement::UPDATE;
  }

  public function create(Sabel_DB_Model_Executer $executer)
  {
    $model     = $executer->getModel();
    $driver    = $executer->getDriver();
    $query     = $driver->loadSqlClass($executer)->buildUpdateSql($driver);
    $manager   = $executer->getConditionManager();
    $this->sql = $query . $manager->build($driver);

    return $this;
  }
}
