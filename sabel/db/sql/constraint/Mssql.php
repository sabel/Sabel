<?php

/**
 * Sabel_DB_Sql_Constraint_Mssql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Constraint_Mssql implements Sabel_DB_Sql_Constraint_Interface
{
  protected $orderColumn = "";

  public function setDefaultOrderColumn($columnName)
  {
    $this->orderColumn = $columnName;
  }

  public function build($sql, $constraints)
  {
    $c = $constraints;
    $skipOrder = false;

    if (isset($c["group"]))  $sql .= " GROUP BY " . $c["group"];
    if (isset($c["having"])) $sql .= " HAVING "   . $c["having"];

    if (isset($c["limit"]) && isset($c["offset"])) {
      $rn   = "row_number() over (order by {$c["order"]}) as rn, ";
      $sql  = "SELECT * FROM ( SELECT " . $rn . substr($sql, 6) . ") tmp";
      $sql .= " WHERE rn between " . ($c["offset"] + 1) . " AND " . ($c["offset"] + $c["limit"]);
      $skipOrder = true;
    } elseif (isset($c["limit"]) && !isset($c["offset"])) {
      $sql = "SELECT TOP " . $c["limit"] . substr($sql, 6);
    } elseif (isset($c["offset"])) {
      $rn   = "row_number() over (order by {$c["order"]}) as rn, ";
      $sql  = "SELECT * FROM ( SELECT " . $rn . substr($sql, 6) . ") tmp";
      $sql .= " WHERE rn > {$c["offset"]}";
      $skipOrder = true;
    }

    if (!$skipOrder) {
      if (isset($c["order"])) $sql .= " ORDER BY " . $c["order"];
    }

    return $sql;
  }
}
