<?php

/**
 * Sabel_DB_Pdo_Sql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Sql extends Sabel_DB_Abstract_Sql
{
  public function buildInsertSql(Sabel_DB_Abstract_Driver $driver)
  {
    $model   = $this->model;
    $tblName = $model->getTableName();
    $values  = $model->getSaveValues();

    $this->emptyCheck($values, "insert");

    $binds = array();
    $data  = (isset($values[0])) ? $values[0] : $values;
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

  public function buildUpdateSql(Sabel_DB_Abstract_Driver $driver)
  {
    $model   = $this->model;
    $tblName = $model->getTableName();
    $values  = $driver->escape($model->getSaveValues());

    $this->emptyCheck($values, "update");

    foreach ($values as $column => $value) {
      $sql[] = "$column = :{$column}";
    }

    $driver->setBindValues($values, false);

    return "UPDATE $tblName SET " . implode(", ", $sql);
  }
}
