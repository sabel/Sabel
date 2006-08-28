<?php

class Sabel_DB_Schema_Pgsql extends Sabel_DB_Schema_MyPg
{
  public function getNumericTypes()
  {
    return array('smallint', 'integer', 'bigint');
  }

  public function getStringTypes()
  {
    return array('character varying', 'varchar', 'character', 'char');
  }

  public function getTextTypes()
  {
    return array('text');
  }

  public function getBinaryTypes()
  {
    return array('bytea');
  }

  public function addIncrementInfo($co, $columnRecord)
  {
    $sql  = "SELECT * FROM pg_statio_user_sequences ";
    $sql .= "WHERE relname = '{$columnRecord['table_name']}_{$co->name}_seq'";
    return $sql;
  }

  public function addPrimaryKeyInfo($co, $columnRecord)
  {
    $sql  = "SELECT * FROM information_schema.key_column_usage ";
    $sql .= "WHERE table_schema = '{$this->schema}' AND ";
    $sql .= "table_name = '{$columnRecord['table_name']}' AND column_name = '{$co->name}'";

    return $sql;
  }

  public function addCommentInfo($co, $columnRecord)
  {
    $sqls   = array();
    $sqls[] = "SELECT relfilenode FROM pg_class WHERE relname = '{$columnRecord['table_name']}'";

    $sql  = "SELECT ordinal_position FROM information_schema.columns ";
    $sql .= "WHERE table_name = '{$columnRecord['table_name']}' AND column_name = '{$co->name}'";

    $sqls[] = $sql;
    $sqls[] = "SELECT col_description FROM col_description(%s, %s)";
    return $sqls;
  }

  public function addStringLength($co, $columnRecord)
  {
    $maxlen = $columnRecord['character_maximum_length'];
    $co->maxLength = (isset($maxlen)) ? $maxlen : 65535;
  }
}
