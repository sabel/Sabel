<?php

class Sabel_DB_Schema_Pgsql extends Sabel_DB_Schema_General
{
  protected
    $tableList    = "SELECT table_name FROM information_schema.tables WHERE table_schema = '%s'",
    $tableColumns = "SELECT * FROM information_schema.columns WHERE table_schema = '%s' AND table_name = '%s'";

  public function isBoolean($type, $row)
  {
    return ($type === 'boolean');
  }

  public function isFloat($type)
  {
    return ($type === 'real' || $type === 'double precision');
  }

  public function getFloatType($type)
  {
    return ($type === 'real') ? 'float' : 'double';
  }

  public function setDefault($co, $row)
  {
    $default = $row['column_default'];

    if (is_null($default) || strpos($default, 'nextval') !== false) {
      $co->default = null;
    } else if (is_numeric($default)) {
      $co->default = (int)$default;
    } else if ($co->type === Sabel_DB_Schema_Const::BOOL) {
      $co->default = ($default === 'true');
    } else {
      $default     = substr($default, 1);
      $co->default = substr($default, 0, strpos($default, "'"));
    }
  }

  public function setIncrement($co, $row)
  {
    $sql  = "SELECT * FROM pg_statio_user_sequences ";
    $sql .= "WHERE relname = '{$row['table_name']}_{$co->name}_seq'";

    $co->increment = ($this->execute($sql) !== false);
  }

  public function setPrimaryKey($co, $row)
  {
    $sql  = "SELECT * FROM information_schema.key_column_usage ";
    $sql .= "WHERE table_schema = '{$this->schema}' AND table_name = '{$row['table_name']}' ";
    $sql .= "AND column_name = '{$co->name}' AND constraint_name LIKE '%\_pkey'";

    $co->primary = ($this->execute($sql) !== false);
  }

  public function setLength($co, $row)
  {
    $maxlen  = $row['character_maximum_length'];
    $co->max = (isset($maxlen)) ? $maxlen : 255;
  }
}
