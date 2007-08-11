<?php

/**
 * Sabel_DB_Sql_Statement_Delete
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Statement_Delete extends Sabel_DB_Abstract_Statement
{
  public function getStatementType()
  {
    return Sabel_DB_Statement::DELETE;
  }

  public function create(Sabel_DB_Model_Executer $executer)
  {
    $model     = $executer->getModel();
    $driver    = $executer->getDriver();
    $query     = "DELETE FROM " . $model->getTableName();
    $this->sql = $query . $executer->getConditionManager()->build($driver);

    return $this;
  }
}
