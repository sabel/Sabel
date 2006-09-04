<?php

class Parser
{
  public static function create($schema)
  {
    $columns = array();

    if (is_object($schema))
      $schema = $schema->getCreateSQL();

    foreach ($schema as $name => $info) {
      $co = new ValueObject();

      $split = explode(',', $info);
      $type  = $split[0];

      $co->name = $name;
      $co->type = $type;
      unset($split[0]);

      if ($type === Sabel_DB_Schema_Type::INT) {
        $co->max = (float)$split[1];
        $co->min = (float)$split[2];
        unset($split[1]);
        unset($split[2]);
      } else if ($type === Sabel_DB_Schema_Type::STRING) {
        $co->max       = (int)$split[1];
        unset($split[1]);
      }

      $split = array_values($split);

      $co->increment = ($split[0] === 'true');
      $co->notNull   = ($split[1] === 'true');
      $co->primary   = ($split[2] === 'true');
      $co->default   = $split[3];

      $columns[$name] = $co;
    }

    return $columns;
  }
}
