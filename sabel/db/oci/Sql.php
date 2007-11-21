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
  protected $placeHolderPrefix = ":";
  protected $placeHolderSuffix = "";

  public function escape(array $values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . addcslashes(str_replace("'", "''", $val), "\000\032\\\n\r") . "'";
      } elseif (is_object($val)) {
        $val = $this->escapeObject($val);
      }
    }

    return $values;
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

  protected function createConstraintSql()
  {
    $sql = "";
    $c = $this->constraints;

    if (isset($c["group"]))  $sql .= " GROUP BY " . $c["group"];
    if (isset($c["having"])) $sql .= " HAVING "   . $c["having"];
    if (isset($c["order"]))  $sql .= " ORDER BY " . $c["order"];

    $limit  = (isset($c["limit"]))  ? $c["limit"]  : null;
    $offset = (isset($c["offset"])) ? $c["offset"] : null;

    $this->driver->setLimit($limit);
    $this->driver->setOffset($offset);

    return $sql;
  }
}
