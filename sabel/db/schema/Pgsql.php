<?php

class Sabel_DB_Schema_Pgsql extends Sabel_DB_Schema_MyPg
{
  public function addDefaultInfo($co, $default)
  {
    if ((strpos($default, 'nextval') === false)) {
      if (ctype_digit($default)) {
        $co->default = (int)$default;
      } else {
        $default     = substr($default, 1);
        $co->default = substr($default, 0, strpos($default, "'"));
      }
    } else {
      $co->default = null;
    }
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

  public function isBoolean($type, $columnRecord)
  {
    return ($type === 'boolean');
  }

  public function addStringLength($co, $columnRecord)
  {
    $maxlen  = $columnRecord['character_maximum_length'];
    $co->max = (isset($maxlen)) ? $maxlen : 256;
  }
}
