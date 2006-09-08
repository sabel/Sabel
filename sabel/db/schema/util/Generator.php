<?php
/*
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
*/
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

    $dbCon = array();
    $connectName       = $_SERVER['argv'][1];
    $dbCon['driver']   = $_SERVER['argv'][2];

    if ($dbCon['driver'] === 'pdo-sqlite') {
      $dbCon['database'] = $_SERVER['argv'][3];
      $dirPath = $_SERVER['argv'][4] . '/';
    } else {
      $dbCon['host']     = $_SERVER['argv'][3];
      $dbCon['database'] = $_SERVER['argv'][4];
      $dbCon['user']     = $_SERVER['argv'][5];

      if (count($_SERVER['argv']) === 9) {
        $dbCon['password'] = $_SERVER['argv'][6];
        $dbCon['schema']   = $_SERVER['argv'][7];
        $dirPath = $_SERVER['argv'][8] . '/';
      } else {
        $dbCon['password'] = '';
        $dbCon['schema']   = $_SERVER['argv'][6];
        $dirPath = $_SERVER['argv'][7] . '/';
      }
    }

    Sabel_DB_Connection::addConnection($connectName, $dbCon);
    $sa = new Sabel_DB_Schema_Accessor($connectName, $dbCon['schema']);
    $schemas = $sa->getTables();

    foreach ($schemas as $schema) {
      $tName = $schema->getTableName();
      ParsedSQL_Writer::write($connectName, $tName, ParsedSQL_Maker::make($schema), $dirPath);
    }
  }
}

/**
 * on sabel
 *
if (!isset($_SERVER['argv'][1]) || !isset($_SERVER['argv'][2]) || !isset($_SERVER['argv'][3])) {
  echo "usage: php Generator.php [environment] [connectname] [dirpath]\n\n";
  exit;
}
//*/

/**
 * use only sabel_db pakage.
 *
 *
if (count($_SERVER['argv']) < 8 && $_SERVER['argv'][2] !== 'pdo-sqlite') {
  echo "usage mysql|postgres|firebird:\n";
  echo "  php Generator.php [connectname] [driver] [host] [dbname] [dbuser] ([password]) [schema] [dirpath]\n\n";
  echo "usage sqlite:\n";
  echo "  php Generator.php [connectname] [driver] [dbname] [dirpath]\n\n";
  exit;
}
 //*/

//Schema_Generator::main();
