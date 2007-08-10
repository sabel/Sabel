<?php

/**
 * Sabel_DB_Abstract_Sql
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Sql
{
  protected $executer = null;
  protected $model    = null;

  public function setExecuter($executer)
  {
    $this->executer = $executer;
    $this->model    = $executer->getModel();
  }

  public function buildSelectSql($driver)
  {
    $model      = $this->model;
    $executer   = $this->executer;
    $tblName    = $model->getTableName();
    $projection = $executer->getProjection();

    if ($projection === "*") {
      $projection = implode(", ", $model->getColumnNames());
    }

    $sql = "SELECT $projection FROM $tblName";

    $cmanager = $executer->getConditionManager();
    if (is_object($cmanager)) $sql .= $cmanager->build($driver);

    if ($constraints = $executer->getConstraints()) {
      return $driver->loadConstraintSqlClass()->build($sql, $constraints);
    } else {
      return $sql;
    }
  }

  protected function emptyCheck($values, $method)
  {
    if (empty($values)) {
      $e = new Sabel_DB_Exception_Sql();
      throw $e->exception("build" . ucfirst($method) . "Sql", "empty $method values.");
    } else {
      return true;
    }
  }
}
