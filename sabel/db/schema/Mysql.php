<?php

class Sabel_DB_Schema_Mysql extends Sabel_DB_Schema_MyPg
{
  public function getNumericTypes()
  {
    return array('int', 'bigint', 'smallint', 'tinyint', 'mediumint');
  }

  public function getStringTypes()
  {
    return array('varchar', 'char');
  }

  public function getTextTypes()
  {
    return array('text', 'mediumtext', 'tinytext');
  }

  public function getBinaryTypes()
  {
    return array('blob', 'mediumblob', 'longblob');
  }

  public function getTimestampTypes()
  {
    return array('timestamp', 'datetime');
  }

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

  public function addCommentInfo($co, $columnRecord)
  {
    $co->comment = $columnRecord['column_comment'];
  }

  public function addStringLength($co, $columnRecord)
  {
    $co->max = $columnRecord['character_maximum_length'];
  }
}
