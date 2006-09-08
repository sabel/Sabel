<?php

class Sabel_DB_Schema_Mysql extends Sabel_DB_Schema_MyPg
{
  public function addDefaultInfo($co, $default)
  {
    $co->default = $default;
  }

  public function addIncrementInfo($co, $columnRecord)
  {
    $co->increment = ($columnRecord['extra'] === 'auto_increment');
  }

  public function addPrimaryKeyInfo($co, $columnRecord)
  {
    $co->primary = ($columnRecord['column_key'] === 'PRI');
  }

  public function isBoolean($type, $columnRecord)
  {
    return ($type === 'tinyint' && $columnRecord['column_comment'] === 'boolean');
  }

  public function addStringLength($co, $columnRecord)
  {
    $co->max =(int) $columnRecord['character_octet_length'];
  }
}
