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
class Sabel_DB_Mysql_Schema extends Sabel_DB_Abstract_Schema
{
  public function getTable($tblName)
  {
    $schema = parent::getTable($tblName);
    $schema->setTableEngine($this->getTableEngine($tblName));

    return $schema;
  }

  public function getTableList()
  {
    $sql = "SELECT table_name FROM information_schema.tables "
         . "WHERE table_schema = '{$this->schemaName}'";

    $rows = $this->execute($sql);
    if (empty($rows)) return array();

    $tables = array();
    foreach ($rows as $row) {
      $row = array_change_key_case($row);
      $tables[] = $row["table_name"];
    }

    return $tables;
  }

  protected function createColumns($tblName)
  {
    $sql = "SELECT column_name, data_type, is_nullable, "
         . "column_default, column_comment, column_key, "
         . "column_type, character_maximum_length, extra "
         . "FROM information_schema.columns "
         . "WHERE table_schema = '{$this->schemaName}' "
         . "AND table_name = '{$tblName}'";

    $rows = $this->execute($sql);
    if (empty($rows)) return array();

    $columns = array();
    foreach ($rows as $row) {
      $colName = $row["column_name"];
      $columns[$colName] = $this->createColumn($row);
    }

    return $columns;
  }

  protected function createColumn($row)
  {
    $column = new Sabel_DB_Schema_Column();
    $column->name = $row["column_name"];
    $column->nullable = ($row["is_nullable"] !== "NO");

    if ($row["column_type"] === "tinyint(1)") {
      $column->type = Sabel_DB_Type::BOOL;
    } else {
      Sabel_DB_Type_Setter::send($column, $row["data_type"]);
    }

    $this->setDefault($column, $row["column_default"]);
    $column->primary   = ($row["column_key"] === "PRI");
    $column->increment = ($row["extra"] === "auto_increment");

    if ($column->primary) {
      $column->nullable = false;
    }

    if ($column->isString()) {
      $column->max = (int)$row["character_maximum_length"];
    }

    return $column;
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
    $schema = $this->schemaName;

    $sql = "SELECT tc.constraint_name as unique_key, kcu.column_name "
         . "FROM information_schema.table_constraints tc "
         . "INNER JOIN information_schema.key_column_usage kcu ON "
         . "tc.constraint_name = kcu.constraint_name "
         . "WHERE tc.constraint_schema = kcu.constraint_schema "
         . "AND tc.table_name='{$tblName}' AND kcu.table_name='{$tblName}' "
         . "AND tc.constraint_schema = '{$schema}' AND tc.constraint_type='UNIQUE'";

    $rows = $this->execute($sql);
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

  private function getForeignKeys50($tblName)
  {
    $schemaName = $this->schemaName;
    $result     = $this->execute("SHOW CREATE TABLE $tblName");
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

  private function getRule($rule, $ruleName)
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

  private function getForeignKeys51($tblName)
  {
    $driver = $this->driver;
    $schema = $this->schemaName;

    $sql = "SELECT kcu.column_name, kcu.referenced_table_name as ref_table, "
         . "kcu.referenced_column_name ref_column, refc.delete_rule, "
         . "refc.update_rule FROM information_schema.table_constraints tc "
         . "INNER JOIN information_schema.referential_constraints refc ON "
         . "refc.constraint_name = tc.constraint_name "
         . "INNER JOIN information_schema.key_column_usage kcu ON "
         . "kcu.constraint_name = tc.constraint_name "
         . "WHERE tc.constraint_schema = '{$schema}' AND tc.table_name = '{$tblName}'"
         . "AND tc.constraint_type = 'FOREIGN KEY'";

    $rows = $driver->execute($sql);
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

  private function setDefault($co, $default)
  {
    if ($default === null || $co->isString() && $default === "") {
      $co->default = null;
    } else {
      $this->setDefaultValue($co, $default);
    }
  }
}
