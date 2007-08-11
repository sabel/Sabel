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
  protected $model = null;

  abstract public function buildInsertSql(Sabel_DB_Abstract_Driver $driver);
  abstract public function buildUpdateSql(Sabel_DB_Abstract_Driver $driver);

  public function setModel(Sabel_DB_Model $model)
  {
    $this->model = $model;
  }

  public function buildSelectSql(Sabel_DB_Abstract_Driver $driver, $projection = "*")
  {
    $model   = $this->model;
    $tblName = $model->getTableName();

    if ($projection === "*") {
      $projection = implode(", ", $model->getColumnNames());
    }

    return "SELECT $projection FROM $tblName";
  }

  protected function emptyCheck($values, $method)
  {
    if (empty($values)) {
      $message = "build" . ucfirst($method) . "Sql() empty $method values.";
      throw new Sabel_DB_Exception($message);
    } else {
      return true;
    }
  }
}
