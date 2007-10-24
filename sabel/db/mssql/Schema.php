<?php

/**
 * Sabel_DB_Mssql_Schema
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mssql_Schema extends Sabel_DB_Abstract_Schema
{
  public function getTableList()
  {
    $sql  = "SELECT table_name FROM information_schema.tables "
          . "WHERE table_catalog = '{$this->schemaName}'";

    $rows = $this->execute($sql);
    if (empty($rows)) return array();

    $tables = array();
    foreach ($rows as $row) {
      // $row = array_change_key_case($row);
      $tables[] = $row["table_name"];
    }

    return $tables;
  }

  protected function createColumns($tblName)
  {
    $sql = "SELECT table_name, column_name, is_nullable, "
         . "column_default, character_octet_length "
         . "FROM information_schema.columns "
         . "WHERE table_catalog = '{$this->schemaName}' "
         . "AND table_name = '{$tblName}'";

    $rows = $this->execute($sql);
    if (empty($rows)) return array();

    $columns = array();
    foreach ($rows as $row) {
      // $row = array_change_key_case($row);
      $colName = $row["column_name"];
      $columns[$colName] = $this->makeColumnValueObject($row);
    }

    return $columns;
  }

  protected function makeColumnValueObject($row)
  {
    $column = new Sabel_DB_Schema_Column();
    $column->name = $row["column_name"];
    $column->nullable = ($row["is_nullable"] !== "NO");
    Sabel_DB_Type_Setter::send($column, $row["data_type"]);

    $this->setDefault($column, $row["column_default"]);

    // @todo
    // $this->setIncrement($column, $row);
    // $this->setPrimaryKey($column, $row);

    if ($column->primary) {
      $column->nullable = false;
    }

    if ($column->isString()) {
      $column->max = (int)$row["character_octet_length"];
    }

    return $column;
  }

  /* @todo
  protected function setIncrement($co, $row)
  {
    $sql = "SELECT * from sys.objects obj, sys.identity_columns ident "
         . "WHERE obj.name = '{$row['table_name']}' AND ident.name = '{$co->name}' AND "
         . "obj.object_id = ident.object_id";

    $rows = $this->execute($sql);
    $co->increment = !(empty($rows));
  }

  protected function setPrimaryKey($co, $row)
  {
    $sql = "SELECT const.type FROM information_schema.constraint_column_usage col, "
         . "sys.key_constraints const WHERE col.table_catalog = '{$this->schemaName}' "
         . "AND col.table_name = '{$row['table_name']}' AND "
         . "col.column_name = '{$co->name}' AND col.constraint_name = const.name";

    $rows = $this->execute($sql);

    if (empty($rows)) {
      $co->primary = false;
    } else {
      $co->primary = ($rows[0]["type"] === "PK");
    }
  }
  */

  private function setDefault($column, $default)
  {
    if ($default === null) {
      $column->default = null;
    } else {
      $default = substr($default, 2, -2);
      $this->setDefaultValue($column, $default);
    }
  }
}
