<?php

/**
 * Sabel_DB_Sql_Pdo
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Pdo extends Sabel_DB_Sql_Base
{
  public function buildInsertSql($driver)
  {
    $model   = $this->model;
    $tblName = $model->getTableName();
    $values  = $model->getSaveValues();

    $data  = (isset($values[0])) ? $values[0] : $values;

    $binds = array();
    $keys  = array_keys($data);

    foreach ($keys as $key) $binds[] = ":" . $key;

    $sql = array("INSERT INTO $tblName (");
    $sql[] = join(", ", $keys);
    $sql[] = ") VALUES(";
    $sql[] = join(", ", $binds);
    $sql[] = ")";

    $sql = implode("", $sql);

    if (isset($values[0])) {
      $sqls = array();
      foreach ($values as &$vals) {
        $vals   = $driver->escape($vals);
        $sqls[] = $sql;
      }
      $sql = $sqls;
    } else {
      $values = $driver->escape($values);
    }

    $driver->setBindValues($values, false);
    return $sql;
  }

  public function buildUpdateSql($driver)
  {
    $model   = $this->model;
    $tblName = $model->getTableName();
    $values  = $driver->escape($model->getSaveValues());

    foreach ($values as $column => $value) {
      $sql[] = "$column = :{$column}";
    }

    $driver->setBindValues($values, false);

    $sql = "UPDATE $tblName SET " . implode(", ", $sql);
    return $sql . $this->getConditionForUpdate($driver);
  }
}
