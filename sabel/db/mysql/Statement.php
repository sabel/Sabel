<?php

/**
 * Sabel_DB_Mysql_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysql_Statement extends Sabel_DB_Abstract_Statement
{
  public function values(array $values)
  {
    if ($this->isInsert()) {
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
    $conn = $this->driver->getConnection();

    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . mysql_real_escape_string($val, $conn) . "'";
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
