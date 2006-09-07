<?php

require_once 'Sabel/sabel/db/Connection.php';

require_once 'Sabel/sabel/db/query/Interface.php';
require_once 'Sabel/sabel/db/query/Factory.php';
require_once 'Sabel/sabel/db/query/Normal.php';
require_once 'Sabel/sabel/db/query/Bind.php';

require_once 'Sabel/sabel/db/driver/Interface.php';
require_once 'Sabel/sabel/db/driver/General.php';
require_once 'Sabel/sabel/db/driver/Mysql.php';
require_once 'Sabel/sabel/db/driver/Pgsql.php';
require_once 'Sabel/sabel/db/driver/Pdo.php';

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

require_once 'Sabel/sabel/db/schema/util/Parser.php';
require_once 'Sabel/sabel/db/schema/util/Creator.php';

require_once 'Sabel/sabel/config/Spyc.php';
require_once 'Sabel/sabel/config/Yaml.php';
require_once 'Sabel/sabel/Classes.php';

class ParsedSQL_Writer
{
  public static function write($connectName, $tName, $schema, $dirPath)
  {
    $className = $connectName . '_' . $tName;
    $fp = fopen("{$dirPath}{$className}.php", 'w');

    fwrite($fp, "<?php\n");
    fwrite($fp, "\n");
    fwrite($fp, "class {$className}\n");
    fwrite($fp, "{\n");
    fwrite($fp, "  public function getParsedSQL()\n");
    fwrite($fp, "  {\n");
    fwrite($fp, '    $sql' . " = array();\n");
    fwrite($fp, "\n");

    foreach ($schema as $cName => $line) {
      fwrite($fp, '    $sql[' . "'{$cName}'] = '{$line}';\n");
    }

    fwrite($fp, '    return $sql;');
    fwrite($fp, "  }\n");
    fwrite($fp, "}\n");

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

      array_push($info, $column->type);

      if ($column->type === Sabel_DB_Schema_Type::INT) {
        array_push($info, $column->max);
        array_push($info, $column->min);
      } else if ($column->type === Sabel_DB_Schema_Type::STRING) {
        array_push($info, $column->max);
      }

      $increment = ($column->increment) ? 'true' : 'false';
      $notNull   = ($column->notNull) ? 'true' : 'false';
      $primary   = ($column->primary) ? 'true' : 'false';
      $default   = (is_null($column->default)) ? 'null' : $column->default;

      array_push($info, $increment);
      array_push($info, $notNull);
      array_push($info, $primary);
      array_push($info, $default);

      $parsed[$column->name] = join(',', $info);
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

    $schemas = Sabel_DB_Mapper::getSchemaAccessor($connectName, $dbCon['schema'])->getTables();

    foreach ($schemas as $schema) {
      $tName = $schema->getTableName();
      ParsedSQL_Writer::write($connectName, $tName, ParsedSQL_Maker::make($schema), $dirPath);
    }
  }
}

Schema_Generator::main();