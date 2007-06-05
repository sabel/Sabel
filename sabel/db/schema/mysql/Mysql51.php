<?php

/**
 * Sabel_DB_Schema_Mysql_Mysql51
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Mysql_Mysql51 extends Sabel_DB_Schema_Mysql_Base
{
  public function getForeignKeys($tblName, $schemaName, $driver)
  {
    $is  = "information_schema";
    $sql = "SELECT kcu.column_name, kcu.referenced_table_name as ref_table, "
         . "kcu.referenced_column_name ref_column, refc.delete_rule, refc.update_rule "
         . "FROM {$is}.table_constraints tc "
         . "INNER JOIN {$is}.referential_constraints refc ON refc.constraint_name = tc.constraint_name "
         . "INNER JOIN {$is}.key_column_usage kcu ON kcu.constraint_name = tc.constraint_name "
         . "WHERE tc.constraint_schema = '{$schemaName}' AND tc.table_name = '{$tblName}'"
         . "AND tc.constraint_type = 'FOREIGN KEY'";

    $rows = $driver->setSql($sql)->execute();
    if (empty($rows)) return null;

    $columns = array();
    foreach ($rows as $row) {
      $column = $row["column_name"];
      $columns[$column]["referenced_table"]  = $row["ref_table"];
      $columns[$column]["referenced_column"] = $row["ref_column"];
      $columns[$column]["on_delete"]         = $row["delete_rule"];
      $columns[$column]["on_update"]         = $row["update_rule"];
    }

    return $columns;
  }
}
