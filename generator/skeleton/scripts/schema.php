<?php

require_once 'Sabel/sabel/db/Connection.php';

require_once 'Sabel/sabel/db/driver/Query.php';
require_once 'Sabel/sabel/db/driver/native/Query.php';
require_once 'Sabel/sabel/db/driver/pdo/Query.php';

require_once 'Sabel/sabel/db/driver/General.php';
require_once 'Sabel/sabel/db/driver/native/Mysql.php';
require_once 'Sabel/sabel/db/driver/native/Pgsql.php';
require_once 'Sabel/sabel/db/driver/pdo/Driver.php';

require_once 'Sabel/sabel/db/Const.php';
require_once 'Sabel/sabel/db/Transaction.php';
require_once 'Sabel/sabel/db/Mapper.php';
require_once 'Sabel/sabel/db/BaseClasses.php';

require_once 'Sabel/sabel/db/schema/Type.php';
require_once 'Sabel/sabel/db/schema/Types.php';
require_once 'Sabel/sabel/db/schema/Setter.php';
require_once 'Sabel/sabel/db/schema/Table.php';
require_once 'Sabel/sabel/db/schema/MyPg.php';
require_once 'Sabel/sabel/db/schema/Mysql.php';
require_once 'Sabel/sabel/db/schema/Pgsql.php';
require_once 'Sabel/sabel/db/schema/SQLite.php';
require_once 'Sabel/sabel/db/schema/Accessor.php';

require_once 'Sabel/sabel/db/schema/util/Creator.php';

require_once 'Sabel/sabel/config/Spyc.php';
require_once 'Sabel/sabel/config/Yaml.php';
require_once 'Sabel/sabel/Classes.php';

class ParsedSQL_Writer
{
  public static function write($connectName, $tName, $schema, $dirPath)
  {
    $className = $connectName . '_' . $tName;
    $target = "{$dirPath}{$className}.php";
    echo "generate {$target} \n";
    $fp = fopen($target, 'w');

    ob_start();
    @include("Schema_Templete.php");
    $contents = ob_get_contents();
    ob_end_clean();
    $contents = str_replace('#php', '?php', $contents);
    fwrite($fp, $contents);
    fclose($fp);
  }
}

class ParsedSQL_Maker
{
  public static function make($schema)
  {
    $parsed  = array();
    $columns = $schema->getColumns();

    foreach ($columns as $column) {
      $info = array();
      array_push($info, '$sql[' . "'{$column->name}'] = array(");
      array_push($info, "'type' => '{$column->type}', ");

      if ($column->type === Sabel_DB_Schema_Type::INT) {
        array_push($info, "'max' => {$column->max}, ");
        array_push($info, "'min' => {$column->min}, ");
      } else if ($column->type === Sabel_DB_Schema_Type::STRING) {
        array_push($info, "'max' => {$column->max}, ");
      }

      $increment = ($column->increment) ? 'true' : 'false';
      $notNull   = ($column->notNull) ? 'true' : 'false';
      $primary   = ($column->primary) ? 'true' : 'false';

      array_push($info, "'increment' => {$increment}, ");
      array_push($info, "'notNull' => {$notNull}, ");
      array_push($info, "'primary' => {$primary}, ");

      if (is_null($column->default)) {
        array_push($info, "'default' => null");
      } else {
        $def = $column->default;
        if (is_int($def) || is_string($def) && ctype_digit($def)) {
          array_push($info, "'default' => {$def}");
        } else {
          array_push($info, "'default' => '{$def}'");
        }
      }

      array_push($info, ");\n");
      $parsed[$column->name] = join('', $info);
    }
    return $parsed;
  }
}

class Schema_Generator
{
  public static function main()
  {
    $sArray = array();

    $environment = $_SERVER['argv'][1];
    $connectName = $_SERVER['argv'][2];
    $dirPath     = $_SERVER['argv'][3];

    $conf  = new Sabel_Config_Yaml('database.yml');
    $dbc   = $conf->read($environment);
    $dbCon = $dbc[$connectName];

    Sabel_DB_Connection::addConnection($connectName, $dbc[$connectName]);

    $sa = new Sabel_DB_Schema_Accessor($connectName, $dbCon['schema']);
    $schemas = $sa->getTables();

    foreach ($schemas as $schema) {
      $tName = $schema->getTableName();
      ParsedSQL_Writer::write($connectName, $tName, ParsedSQL_Maker::make($schema), $dirPath);
    }
  }
}

Schema_Generator::main();
