<?php

class Sabel_DB_Schema_Mysql extends Sabel_DB_Schema_MyPg
{
  public function getNumericTypes()
  {
    return array('tinyint', 'smallint', 'mediumint', 'int', 'bigint');
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

  public function addCommentInfo($co, $columnRecord)
  {
    $co->comment = $columnRecord['column_comment'];
  }

  public function addIncrementInfo($co, $columnRecord)
  {
    $co->increment = ($columnRecord['extra'] === 'auto_increment');
  }

  public function addStringLength($co, $columnRecord)
  {
    $co->maxLength = $columnRecord['character_maximum_length'];
  }
}
