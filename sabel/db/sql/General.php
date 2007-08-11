<?php

/**
 * Sabel_DB_Sql_General
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_General extends Sabel_DB_Abstract_Sql
{
  public function buildInsertSql(Sabel_DB_Abstract_Driver $driver)
  {
    $model   = $this->model;
    $tblName = $model->getTableName();
    $values  = $model->getSaveValues();

    $this->emptyCheck($values, "insert");

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

  private function createInsertSql($tblName, $columns, $values)
  {
    $sql   = array("INSERT INTO $tblName (");
    $sql[] = implode(", ", $columns) . ") VALUES (";

    $vals = array();
    foreach ($values as $val) {
      $vals[] = ($val === null) ? "NULL" : $val;
    }

    $sql[] = implode(", ", $vals) . ")";
    return implode("", $sql);
  }

  public function buildUpdateSql(Sabel_DB_Abstract_Driver $driver)
  {
    $model   = $this->model;
    $tblName = $model->getTableName();
    $values  = $driver->escape($model->getSaveValues());

    $this->emptyCheck($values, "update");

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
