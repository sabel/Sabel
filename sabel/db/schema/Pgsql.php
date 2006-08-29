<?php

class Sabel_DB_Schema_Pgsql extends Sabel_DB_Schema_MyPg
{
  public function getNumericTypes()
  {
    return array('integer', 'bigint', 'smallint');
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

  public function getTimeStampTypes()
  {
    return array('timestamp without time zone', 'timestamp with time zone');
  }

  public function addDefaultInfo($co, $default)
  {
    $default = str_replace(substr(strpbrk($default, '::'), 0), '', $default);
    $co->default = substr($default, 1, strlen($default) - 2);
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
    $sql .= "WHERE table_schema = '{$this->schema}' AND table_name = '{$columnRecord['table_name']}' ";
    $sql .= "AND column_name = '{$co->name}' AND constraint_name LIKE '%\_pkey'";

    return $sql;
  }

  public function addCommentInfo($co, $columnRecord)
  {
    $sqls   = array();
    $sqls[] = "SELECT relfilenode FROM pg_class WHERE relname = '{$columnRecord['table_name']}'";
    $sqls[] = "SELECT col_description FROM col_description(%s, %s)";
    return $sqls;
  }

  public function addStringLength($co, $columnRecord)
  {
    $maxlen  = $columnRecord['character_maximum_length'];
    $co->max = (isset($maxlen)) ? $maxlen : 65535;
  }
}
