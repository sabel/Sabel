<?php

/**
 * Sabel_DB_Schema_Mysql_Base
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Schema_Mysql_Base
{
  abstract public function getForeignKeys($tblName, $schemaName, $driver);

  public function getUniques($tblName, $schemaName, $driver)
  {
    $is  = "information_schema";
    $sql = "SELECT tc.constraint_name as unique_key, kcu.column_name "
         . "FROM {$is}.table_constraints tc "
         . "INNER JOIN {$is}.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name "
         . "WHERE tc.constraint_schema = kcu.constraint_schema AND tc.table_name='{$tblName}' "
         . "AND tc.constraint_schema = '{$schemaName}' AND tc.constraint_type='UNIQUE'";

    $rows = $driver->setSql($sql)->execute();
    if (empty($rows)) return null;

    $uniques = array();
    foreach ($rows as $row) {
      $key = $row["unique_key"];
      $uniques[$key][] = $row["column_name"];
    }

    return array_values($uniques);
  }
}

