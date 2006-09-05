<?php

class Schema_Creator
{
  public static function create($schema)
  {
    $columns = array();

    if (is_object($schema))
      $schema = $schema->getParsedSQL();

    foreach ($schema as $name => $info) {
      $vo    = new ValueObject();
      $split = explode(',', $info);

      $vo->name = $name;
      $vo->type = $split[0];

      if ($vo->type === Sabel_DB_Schema_Type::INT) {
        $vo->max = (float)$split[1];
        $vo->min = (float)$split[2];
      } else if ($vo->type === Sabel_DB_Schema_Type::STRING) {
        $vo->max = (int)$split[1];
      }

      $c = count($split) - 4;
      for ($i = 0; $i < $c; $i++) unset($split[$i]);

      $split = array_values($split);

      $vo->increment = ($split[0] === 'true');
      $vo->notNull   = ($split[1] === 'true');
      $vo->primary   = ($split[2] === 'true');
      $vo->default   = $split[3];

      $columns[$name] = $vo;
    }

    return new Sabel_DB_Schema_Table('table_name', $columns);
  }
}
