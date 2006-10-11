<?php

class Sabel_DB_Schema_Mssql extends Sabel_DB_Schema_General
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

    if (is_null($default)) {
      $co->default = null;
    } else {
      $default = substr($default, 2, -2);
      if ($co->type === Sabel_DB_Schema_Const::BOOL) {
        $co->default = ($default === 'true');
      } else {
        $co->default = (is_numeric($default)) ? (int)$default : $default;
      }
    }
  }

  protected function setIncrement($co, $row)
  {
    $sql  = "SELECT * from sys.objects obj, sys.identity_columns ident ";
    $sql .= "WHERE obj.name = '{$row['table_name']}' AND ident.name = '{$co->name}' AND ";
    $sql .= "obj.object_id = ident.object_id";

    $co->increment = ($this->execute($sql) !== false);
  }

  protected function setPrimaryKey($co, $row)
  {
    $sql  = "SELECT const.type FROM information_schema.constraint_column_usage col, sys.key_constraints const ";
    $sql .= "WHERE col.table_catalog = '{$this->schema}' AND col.table_name = '{$row['table_name']}' AND ";
    $sql .= "col.column_name = '{$co->name}' AND col.constraint_name = const.name";

    if ($result = $this->execute($sql)) {
      $co->primary = ($result[0]->type === 'PK');
    } else {
      $co->primary = false;
    }
  }

  protected function setLength($co, $row)
  {
    $co->max = $row['character_octet_length'];
  }
}
