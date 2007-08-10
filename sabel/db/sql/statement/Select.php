<?php

/**
 * Sabel_DB_Sql_Statement_Select
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Statement_Select extends Sabel_DB_Abstract_Statement
{
  public function getStatementType()
  {
    return Sabel_DB_Statement::SELECT;
  }

  public function create($executer, $sql = null)
  {
    if ($sql === "" || $sql === null) {
      $driver = $executer->getDriver();
      $this->sql = $driver->loadSqlClass($executer)->buildSelectSql($driver);
    } else {
      $model  = $executer->getModel();
      $driver = $executer->getDriver();

      $conditionManager = $executer->loadConditionManager();

      if (!$conditionManager->isEmpty()) {
        $sql .= " " . $conditionManager->build($driver);
      }

      if ($constraints = $executer->getConstraints()) {
        $sql = $driver->loadConstraintSqlClass()->build($sql, $constraints);
      }

      $this->sql = $sql;
    }

    return $this;
  }
}
