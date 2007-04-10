<?php

/**
 * Sabel_DB_Sql_Constraint_Ibase
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Constraint_Ibase implements Sabel_DB_Sql_Constraint_Interface
{
  public function build($sql, $constraints)
  {
    $c = $constraints;

    if (isset($c["group"]))  $sql .= " GROUP BY " . $c["group"];
    if (isset($c["having"])) $sql .= " HAVING "   . $c["having"];

    $tmp   = substr($sql, 6);

    if (isset($c["limit"])) {
      $query  = "FIRST {$c["limit"]} ";
      $query .= (isset($c["offset"])) ? "SKIP " . $c["offset"] : "SKIP 0";
      $sql    = "SELECT " . $query . $tmp;
    } else {
      if (isset($c["offset"])) $sql = "SELECT SKIP " . $c["offset"] . $tmp;
    }

    return $sql;
  }
}
