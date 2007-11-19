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
  protected $placeHolderPrefix = ":";
  protected $placeHolderSuffix = "";

  public function values(array $values)
  {
    if ($this->driver->getDriverId() === "pdo-mysql" && $this->isInsert()) {
      foreach ($this->schema->getColumns() as $colName => $column) {
        if (!isset($values[$colName]) && $this->isVarcharOfDefaultNull($column)) {
          $values[$colName] = null;
        }
      }
    }

    return parent::values($values);
  }

  public function escape(array $values)
  {
    switch ($this->driver->getDriverId()) {
      case "pdo-mysql":
        return $this->mysqlEscape($values);

      case "pdo-pgsql":
        return $this->pgsqlEscape($values);

      case "pdo-sqlite":
        return $this->sqliteEscape($values);
    }
  }
  
  protected function mysqlEscape($values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_object($val)) {
        $val = $this->escapeObject($val);
      }
    }

    return $values;
  }

  protected function pgsqlEscape($values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "t" : "f";
      } elseif (is_object($val)) {
        $val = $this->escapeObject($val);
      }
    }

    return $values;
  }

  public function sqliteEscape($values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "true" : "false";
      } elseif (is_object($val)) {
        $val = $this->escapeObject($val);
      }
    }

    return $values;
  }

  private function isVarcharOfDefaultNull($column)
  {
    return ($column->isString() && $column->default === null);
  }
}
