<?php

/**
 * Sabel_DB_Ibase_SqlConstraint
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Ibase_SqlConstraint implements Sabel_DB_Sql_Constraint_Interface
{
  public function build($sql, $constraints)
  {
    $c = $constraints;

    if (isset($c["group"]))  $sql .= " GROUP BY " . $c["group"];
    if (isset($c["having"])) $sql .= " HAVING "   . $c["having"];
    if (isset($c["order"]))  $sql .= " ORDER BY " . $c["order"];

    $tmp = substr($sql, 6);

    if (isset($c["limit"])) {
      $query  = "FIRST {$c["limit"]} ";
      $query .= (isset($c["offset"])) ? "SKIP " . $c["offset"] : "SKIP 0";
      $sql    = "SELECT " . $query . $tmp;
    } elseif (isset($c["offset"])) {
      if (isset($c["offset"])) $sql = "SELECT SKIP " . $c["offset"] . $tmp;
    }

    return $sql;
  }
}
