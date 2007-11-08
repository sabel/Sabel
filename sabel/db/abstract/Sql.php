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
abstract class Sabel_DB_Abstract_Sql extends Sabel_Object
{
  protected
    $stmt = null;

  protected
    $placeHolderPrefix = "@",
    $placeHolderSuffix = "@";

  public function __construct(Sabel_DB_Abstract_Statement $stmt)
  {
    $this->stmt = $stmt;
  }

  public function getPrefixOfPlaceHelder()
  {
    return $this->placeHolderPrefix;
  }

  public function getSuffixOfPlaceHelder()
  {
    return $this->placeHolderSuffix;
  }

  public function createSelectSql()
  {
    $stmt = $this->stmt;

    $sql = "SELECT " . $stmt->getProjection() . " FROM " . $stmt->getTable()
         . $stmt->getJoin() . $stmt->getWhere();

    return $sql . $this->createConstraintSql($stmt->getConstraints());
  }

  public function createInsertSql()
  {
    $binds  = array();
    $stmt   = $this->stmt;
    $values = $stmt->getValues();
    $keys   = array_keys($values);
    $prefix = $this->placeHolderPrefix;
    $suffix = $this->placeHolderSuffix;

    foreach ($keys as $key) {
      $binds[] = $prefix . $key . $suffix;
    }

    $sql = array("INSERT INTO " . $stmt->getTable() . " (");
    $sql[] = join(", ", $keys);
    $sql[] = ") VALUES(";
    $sql[] = join(", ", $binds);
    $sql[] = ")";

    return implode("", $sql);
  }

  public function createUpdateSql()
  {
    $stmt    = $this->stmt;
    $tblName = $stmt->getTable();
    $where   = $stmt->getWhere();
    $prefix  = $this->placeHolderPrefix;
    $suffix  = $this->placeHolderSuffix;

    $updates = array();
    foreach ($stmt->getValues() as $column => $value) {
      $updates[] = "$column = {$prefix}{$column}{$suffix}";
    }

    return "UPDATE $tblName SET " . implode(", ", $updates) . $where;
  }

  public function createDeleteSql()
  {
    return "DELETE FROM " . $this->stmt->getTable() . $this->stmt->getWhere();
  }

  protected function createConstraintSql($constraints)
  {
    $sql = "";

    if (isset($constraints["group"]))  $sql .= " GROUP BY " . $constraints["group"];
    if (isset($constraints["having"])) $sql .= " HAVING "   . $constraints["having"];
    if (isset($constraints["order"]))  $sql .= " ORDER BY " . $constraints["order"];

    if (isset($constraints["offset"]) && !isset($constraints["limit"])) {
      $sql .= " LIMIT 100 OFFSET " . $constraints["offset"];
    } else {
      if (isset($constraints["limit"]))  $sql .= " LIMIT "  . $constraints["limit"];
      if (isset($constraints["offset"])) $sql .= " OFFSET " . $constraints["offset"];
    }

    return $sql;
  }
}
