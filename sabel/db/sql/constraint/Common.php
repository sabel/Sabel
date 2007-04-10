<?php

/**
 * Sabel_DB_Sql_Constraint_Common
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Constraint_Common implements Sabel_DB_Sql_Constraint_Interface
{
  public function build($sql, $constraints)
  {
    $c = $constraints;

    if (isset($c["group"]))  $sql .= " GROUP BY " . $c["group"];
    if (isset($c["having"])) $sql .= " HAVING "   . $c["having"];
    if (isset($c["order"]))  $sql .= " ORDER BY " . $c["order"];
    if (isset($c["limit"]))  $sql .= " LIMIT "    . $c["limit"];
    if (isset($c["offset"])) $sql .= " OFFSET "   . $c["offset"];

    return $sql;
  }
}
