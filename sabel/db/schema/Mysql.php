<?php

class Sabel_DB_Schema_Mysql extends Sabel_DB_Schema_General
{
  protected
    $tableList    = "SELECT table_name FROM information_schema.tables WHERE table_schema = '%s'",
    $tableColumns = "SELECT * FROM information_schema.columns WHERE table_schema = '%s' AND table_name = '%s'";

  public function isBoolean($type, $row)
  {
    return ($type === 'tinyint' && $row['column_comment'] === 'boolean');
  }

  public function isFloat($type)
  {
    return ($type === 'float' || $type === 'double');
  }

  public function getFloatType($type)
  {
    return ($type === 'float') ? 'float' : 'double';
  }

  public function setDefault($co, $row)
  {
    $default = $row['column_default'];

    if (is_null($default)) {
      $co->default = null;
    } else if ($co->type === Sabel_DB_Schema_Const::BOOL) {
      $co->default = ((int)$default === 1);
    } else {
      $co->default = (is_numeric($default)) ? (int)$default : $default;
    }
  }

  public function setIncrement($co, $row)
  {
    $co->increment = ($row['extra'] === 'auto_increment');
  }

  public function setPrimaryKey($co, $row)
  {
    $co->primary = ($row['column_key'] === 'PRI');
  }

  public function setLength($co, $row)
  {
    $co->max = (int)$row['character_octet_length'];
  }
}
