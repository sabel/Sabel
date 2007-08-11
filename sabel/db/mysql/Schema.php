<?php

/**
 * Sabel_DB_Mysql_Schema
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysql_Schema extends Sabel_DB_Abstract_Common_Schema
{
  protected
    $tableList    = "SELECT table_name FROM information_schema.tables WHERE table_schema = '%s'",
    $tableColumns = "SELECT column_name, data_type, is_nullable, column_default, column_comment,
                     column_key, character_maximum_length, extra FROM information_schema.columns
                     WHERE table_schema = '%s' AND table_name = '%s'";

  public function getTable($tblName)
  {
    $schema = parent::getTable($tblName);
    $schema->setTableEngine($this->getTableEngine($tblName));

    return $schema;
  }

  public function isBoolean($type, $row)
  {
    return ($type === "tinyint" && $row["column_comment"] === "boolean");
  }

  public function isFloat($type)
  {
    return ($type === "float" || $type === "double");
  }

  public function getFloatType($type)
  {
    return ($type === "float") ? "float" : "double";
  }

  public function setDefault($co, $row)
  {
    $default = $row["column_default"];

    if ($default === null) {
      $co->default = null;
    } else {
      $this->setDefaultValue($co, $default);
    }
  }

  public function setIncrement($co, $row)
  {
    $co->increment = ($row["extra"] === "auto_increment");
  }

  public function setPrimaryKey($co, $row)
  {
    $co->primary = ($row["column_key"] === "PRI");
  }

  public function setLength($co, $row)
  {
    $co->max = (int)$row["character_maximum_length"];
  }

  public function getForeignKeys($tblName)
  {
    $exp = explode(".", $this->getMysqlVersion());

    if ($exp[1] === "0") {
      return $this->getForeignKeys50($tblName);
    } else {
      return $this->getForeignKeys51($tblName);
    }
  }

  public function getUniques($tblName)
  {
    $schemaName = $this->schemaName;
    $driver = $this->driver;

    $is  = "information_schema";
    $sql = "SELECT tc.constraint_name as unique_key, kcu.column_name "
         . "FROM {$is}.table_constraints tc "
         . "INNER JOIN {$is}.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name "
         . "WHERE tc.constraint_schema = kcu.constraint_schema AND tc.table_name='{$tblName}' "
         . "AND kcu.table_name='{$tblName}' AND tc.constraint_schema = '{$schemaName}' "
         . "AND tc.constraint_type='UNIQUE'";

    $rows = $driver->setSql($sql)->execute();
    if (empty($rows)) return null;

    $uniques = array();
    foreach ($rows as $row) {
      $key = $row["unique_key"];
      $uniques[$key][] = $row["column_name"];
    }

    return array_values($uniques);
  }

  public function getTableEngine($tblName)
  {
    $row = $this->execute("SHOW TABLE STATUS WHERE Name='{$tblName}'");
    return $row[0]["Engine"];
  }

  // @todo
  protected function getMysqlVersion()
  {
    $result  = $this->execute("SELECT VERSION() AS version");
    $version = $result[0]["version"];

    return $version;
  }

  protected function getForeignKeys50($tblName)
  {
    $driver     = $this->driver;
    $schemaName = $this->schemaName;
    $result     = $driver->setSql("SHOW CREATE TABLE $tblName")->execute();
    $createSql  = $result[0]["Create Table"];

    preg_match_all("/CONSTRAINT .+ FOREIGN KEY (.+)/", $createSql, $matches);
    if (empty($matches[1])) return null;

    $columns = array();
    foreach ($matches[1] as $match) {
      $tmp = explode(")", str_replace(" REFERENCES ", "", $match));
      $column = substr($tmp[0], 2, -1);

      $tmp2 = array_map("trim", explode("(", $tmp[1]));
      $columns[$column]["referenced_table"]  = substr($tmp2[0], 1, -1);
      $columns[$column]["referenced_column"] = substr($tmp2[1], 1, -1);

      $rule = trim($tmp[2]);
      $columns[$column]["on_delete"] = $this->getRule($rule, "ON DELETE");
      $columns[$column]["on_update"] = $this->getRule($rule, "ON UPDATE");
    }

    return $columns;
  }

  protected function getRule($rule, $ruleName)
  {
    if (($pos = strpos($rule, $ruleName)) !== false) {
      $on = substr($rule, $pos + 10);
      if (($pos = strpos($on, " ON")) !== false) {
        $on = substr($on, 0, $pos);
      }

      return trim(str_replace(",", "", $on));
    } else {
      return "NO ACTION";
    }
  }

  protected function getForeignKeys51($tblName)
  {
    $driver     = $this->driver;
    $schemaName = $this->schemaName;

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
