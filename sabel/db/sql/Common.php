<?php

/**
 * Sabel_DB_Sql_Common
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Common implements Sabel_DB_Sql_Interface
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

    return "SELECT " . $model->getProjection() . " FROM " . $tblName;
  }

  public function buildInsertSql($driver)
  {
    $model   = $this->model;
    $tblName = $model->getTableName();
    $values  = $model->getSaveValues();

    if (isset($values[0])) {
      $sqls = array();
      $cols = array_keys($values[0]);
      foreach ($values as $vals) {
        $sqls[] = $this->createInsertSql($tblName, $cols, $driver->escape($vals));
      }
      return $sqls;
    } else {
      $cols = array_keys($values);
      return $this->createInsertSql($tblName, $cols, $driver->escape($values));
    }
  }

  protected function createInsertSql($tblName, $columns, $values)
  {
    $sql   = array("INSERT INTO $tblName (");
    $sql[] = implode(", ", $columns);
    $sql[] = ") VALUES(";
    $sql[] = implode(", ", $values);
    $sql[] = ")";

    return implode("", $sql);
  }

  public function buildUpdateSql($driver)
  {
    $model   = $this->model;
    $tblName = $model->getTableName();
    $values  = $driver->escape($model->getSaveValues());

    foreach ($values as $column => $value) {
      if ($value === null) {
        $sql[] = "$column = NULL";
      } else {
        $sql[] = "$column = $value";
      }
    }

    return "UPDATE $tblName SET " . implode(", ", $sql);
  }
}
