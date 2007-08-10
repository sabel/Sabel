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
class Sabel_DB_Mssql_Schema extends Sabel_DB_Abstract_Common_Schema
{
  protected
    $tableList    = "SELECT table_name FROM information_schema.tables WHERE table_catalog = '%s'",
    $tableColumns = "SELECT * FROM information_schema.columns WHERE table_catalog = '%s' AND table_name = '%s'";

  protected function isBoolean($type, $row)
  {
    return ($type === "bit");
  }

  public function isFloat($type)
  {
    return ($type === "real" || $type === "float");
  }

  public function getFloatType($type)
  {
    return ($type === "real") ? "float" : "double";
  }

  protected function setDefault($co, $row)
  {
    $default = $row["column_default"];

    if ($default === null) {
      $co->default = null;
    } else {
      $default = substr($default, 2, -2);
      $this->setDefaultValue($co, $default);
    }
  }

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

  protected function setLength($co, $row)
  {
    $co->max = $row["character_octet_length"];
  }
}
