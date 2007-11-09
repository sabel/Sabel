<?php

/**
 * Sabel_DB_Oci_Sql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Oci_Sql extends Sabel_DB_Abstract_Sql
{
  public function createSelectSql()
  {
    static $nlsDateFormat = null;

    $schema = Sabel_DB_Schema::get($this->table, $this->connectionName);

    foreach ($schema->getColumns() as $column) {
      if ($column->isDatetime() && $nlsDateFormat !== "datetime") {
        $this->driver->execute("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
        $nlsDateFormat = "datetime";
        break;
      } elseif ($column->isDate() && $nlsDateFormat !== "date") {
        $this->driver->execute("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'");
        $nlsDateFormat = "date";
        break;
      }
    }

    return parent::createSelectSql();
  }

  public function createInsertSql()
  {
    if (($column = $this->seqColumn) !== null) {
      $seqName = strtoupper("{$this->table}_{$column}_seq");
      $rows = $this->driver->execute("SELECT {$seqName}.nextval AS id FROM dual");
      $id = $rows[0]["id"];
      $values = array_merge($this->values, array($column => $id));
      $this->values($values);
      $this->driver->setLastInsertId($id);
    }

    return parent::createInsertSql();
  }

  protected function createConstraintSql($constraints)
  {
    $sql = "";

    if (isset($constraints["group"]))  $sql .= " GROUP BY " . $constraints["group"];
    if (isset($constraints["having"])) $sql .= " HAVING "   . $constraints["having"];
    if (isset($constraints["order"]))  $sql .= " ORDER BY " . $constraints["order"];

    $limit  = (isset($constraints["limit"]))  ? $constraints["limit"]  : null;
    $offset = (isset($constraints["offset"])) ? $constraints["offset"] : null;

    $this->driver->setLimit($limit);
    $this->driver->setOffset($offset);

    return $sql;
  }
}
