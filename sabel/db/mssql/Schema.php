<?php

Sabel::using('Sabel_DB_General_Schema');

/**
 * Sabel_DB_Mssql_Schema
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage mssql
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mssql_Schema extends Sabel_DB_General_Schema
{
  protected
    $tableList    = "SELECT table_name FROM information_schema.tables WHERE table_catalog = '%s'",
    $tableColumns = "SELECT * FROM information_schema.columns WHERE table_catalog = '%s' AND table_name = '%s'";

  protected function isBoolean($type, $row)
  {
    return ($type === 'bit');
  }

  public function isFloat($type)
  {
    return ($type === 'real' || $type === 'float');
  }

  public function getFloatType($type)
  {
    return ($type === 'real') ? 'float' : 'double';
  }

  protected function setDefault($co, $row)
  {
    $default = $row['column_default'];

    if ($default === null) {
      $co->default = null;
    } else {
      $default = substr($default, 2, -2);
      if ($co->type === Sabel_DB_Type_Const::BOOL) {
        $co->default = ($default === 'true');
      } else {
        $co->default = (is_numeric($default)) ? (int)$default : $default;
      }
    }
  }

  protected function setIncrement($co, $row)
  {
    $sql  = "SELECT * from sys.objects obj, sys.identity_columns ident "
          . "WHERE obj.name = '{$row['table_name']}' AND ident.name = '{$co->name}' AND "
          . "obj.object_id = ident.object_id";

    $co->increment = (!$this->execute($sql)->isEmpty());
  }

  protected function setPrimaryKey($co, $row)
  {
    $sql  = "SELECT const.type FROM information_schema.constraint_column_usage col, sys.key_constraints const "
          . "WHERE col.table_catalog = '{$this->schema}' AND col.table_name = '{$row['table_name']}' AND "
          . "col.column_name = '{$co->name}' AND col.constraint_name = const.name";

    $resultSet = $this->execute($sql);
    if ($resultSet->isEmpty()) {
      $co->primary = false;
    } else {
      $row = $resultSet->fetch();
      $co->primary = ($row['type'] === 'PK');
    }
  }

  protected function setLength($co, $row)
  {
    $co->max = $row['character_octet_length'];
  }
}
