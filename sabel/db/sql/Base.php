<?php

/**
 * Sabel_DB_Sql_Base
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Sql_Base
{
  protected $model = null;

  public function setModel($model)
  {
    $this->model = $model;
  }

  public function buildSelectSql($driver)
  {
    $model      = $this->model;
    $tblName    = $model->getTableName();
    $projection = $model->getProjection();

    if ($projection === "*") {
      $projection = implode(", ", $model->getColumnNames());
    }

    $sql = "SELECT $projection FROM $tblName";

    $cmanager = $model->getConditionManager();
    if (is_object($cmanager)) $sql .= $cmanager->build($driver);

    if ($constraints = $model->getConstraints()) {
      return $driver->getConstraintSqlClass()->build($sql, $constraints);
    } else {
      return $sql;
    }
  }

  protected function getConditionForUpdate($driver)
  {
    $conds = $this->model->getConditionManager()->getUniqueConditions();

    $manager = new Sabel_DB_Condition_Manager();
    foreach ($conds as $condition) $manager->add($condition);

    return $manager->build($driver);
  }

  abstract public function buildInsertSql($driver);
  abstract public function buildUpdateSql($driver);
}
