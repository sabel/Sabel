<?php

/**
 * Sabel_DB_Mysql_Sql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysql_Sql extends Sabel_DB_Abstract_Sql
{
  public function values(array $values)
  {
    if ($this->isInsert()) {
      foreach ($this->schema->getColumns() as $colName => $column) {
        if ($this->isNullableVarchar($column) && !isset($values[$colName])) {
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
      } elseif (is_object($val)) {
        $val = $this->escapeObject($val);
      } elseif (is_string($val)) {
        $val = "'" . mysql_real_escape_string($val, $conn) . "'";
      }
    }

    return $values;
  }

  private function isNullableVarchar($column)
  {
    return ($column->isString() && $column->default === null);
  }
}
