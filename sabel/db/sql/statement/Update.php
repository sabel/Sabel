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

  public function create($executer, $conditions = array())
  {
    $model  = $executer->getModel();
    $driver = $executer->getDriver();
    $query  = $driver->loadSqlClass($executer)->buildUpdateSql($driver);

    if (empty($conditions)) {
      $pkey = $model->getPrimaryKey();
      if (is_string($pkey)) $pkey = (array)$pkey;

      $conditions = array();
      foreach ($pkey as $key) {
        $conditions[] = new Sabel_DB_Condition_Object($key, $model->__get($key));
      }
    }

    $manager = new Sabel_DB_Condition_Manager();
    foreach ($conditions as $condition) $manager->add($condition);
    $this->sql = $query . $manager->build($driver);

    return $this;
  }
}
