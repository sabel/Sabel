<?php

/**
 * Sabel_DB_Schema_Pgsql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Pgsql extends Sabel_DB_Schema_Common
{
  protected
    $tableList    = "SELECT table_name FROM information_schema.tables WHERE table_schema = '%s'",
    $tableColumns = "SELECT * FROM information_schema.columns WHERE table_schema = '%s' AND table_name = '%s'";

  public function isBoolean($type, $row)
  {
    return ($type === "boolean");
  }

  public function isFloat($type)
  {
    return ($type === "real" || $type === "double precision");
  }

  public function getFloatType($type)
  {
    return ($type === "real") ? "float" : "double";
  }

  public function setDefault($co, $row)
  {
    $default = $row["column_default"];

    if ($default === null || strpos($default, "nextval") !== false) {
      $co->default = null;
    } else {
      if (strpos($default, "'::") !== false) {
        preg_match("/'(.*)'/", $default, $matches);
        $default = $matches[1];
      }

      $this->setDefaultValue($co, $default);
    }
  }

  public function setIncrement($co, $row)
  {
    $sql = "SELECT * FROM pg_statio_user_sequences "
         . "WHERE relname = '{$row["table_name"]}_{$co->name}_seq'";

    $result = $this->execute($sql);
    $co->increment = !(empty($result));
  }

  public function setPrimaryKey($co, $row)
  {
    static $pkeys = array();

    $tblName = $row["table_name"];
    if (isset($pkeys[$tblName])) {
      $keys = $pkeys[$tblName];
    } else {
      $sql = "SELECT column_name FROM information_schema.key_column_usage "
           . "WHERE table_schema = '{$this->schemaName}' "
           . "AND table_name = '{$row["table_name"]}' AND constraint_name LIKE '%\_pkey'";

      $result = $this->execute($sql);

      if (empty($result)) {
        $keys = $pkeys[$tblName] = array();
      } else {
        $keys = array();
        foreach ($result as $row) $keys[] = $row["column_name"];
        $pkeys[$tblName] = $keys;
      }
    }

    $co->primary = in_array($co->name, $keys);
  }

  public function setLength($co, $row)
  {
    $maxlen  = $row["character_maximum_length"];
    $co->max = (isset($maxlen)) ? (int)$maxlen : 255;
  }

  public function getForeignKey($tblName)
  {
    $is  = "information_schema";
    $cn  = "constraint_name";
    $ij  = "INNER JOIN";

    $sql = "SELECT kcu.column_name, ccu.table_name AS ref_table, "
         . "ccu.column_name AS ref_column, rc.delete_rule, rc.update_rule "
         . "FROM {$is}.table_constraints tc "
         . "{$ij} {$is}.constraint_column_usage ccu ON tc.{$cn} = ccu.{$cn} "
         . "{$ij} {$is}.key_column_usage kcu ON tc.{$cn} = kcu.{$cn} "
         . "{$ij} {$is}.referential_constraints rc ON tc.{$cn} = rc.{$cn} "
         . "WHERE tc.table_schema = '{$this->schemaName}' AND tc.table_name = '{$tblName}' "
         . "AND tc.constraint_type = 'FOREIGN KEY'";

    $rows = $this->execute($sql);
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

  public function getUniques($tblName)
  {
    $is  = "information_schema";
    $sql = "SELECT tc.constraint_name, kcu.column_name "
         . "FROM {$is}.table_constraints tc "
         . "INNER JOIN {$is}.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name "
         . "WHERE tc.table_schema = '{$this->schemaName}' AND tc.table_name = '{$tblName}' "
         . "AND tc.constraint_type = 'UNIQUE'";

    $rows = $this->execute($sql);
    if (empty($rows)) return null;

    $uniques = array();
    foreach ($rows as $row) {
      $key = $row["constraint_name"];
      $uniques[$key][] = $row["column_name"];
    }

    return array_values($uniques);
  }
}
