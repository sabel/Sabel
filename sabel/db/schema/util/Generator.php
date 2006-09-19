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
require_once 'Sabel/sabel/cache/Apc.php';
require_once 'Sabel/sabel/Classes.php';
*/
class Cascade_Writer
{
  private static $references  = array();
  private static $foreignKeys = array();

  public static function addForeignKey($connectName, $table, $key)
  {
    self::$foreignKeys[$connectName][$table][] = $key;
  }

  public static function write($dirPath)
  {
    foreach (self::$foreignKeys as $connectName => $tables) {
      foreach ($tables as $tName => $foreignKeys) {
        foreach ($foreignKeys as $key) {
          $parent = str_replace('_id', '', $key);
          self::$references[$connectName][$parent][] = $tName;
        }
      }
    }

    $chain = array();

    foreach (self::$references as $connectName => $table) {
      foreach ($table as $tName => $children) {
        $tName = self::selectConnectName($tName, $connectName);
        foreach ($children as $child) {
          $child = self::selectConnectName($child, $connectName);
          $chain[$tName][] = $child;
        }
      }
    }

    $target = "{$dirPath}/Cascade_Chain.php";
    echo "generate Cascade Chain\n\n";
    $fp = fopen($target, 'w');

    fwrite($fp, "<?php\n\n");
    fwrite($fp, "class Cascade_Chain\n");
    fwrite($fp, "{\n");
    fwrite($fp, "  public function get()\n");
    fwrite($fp, "  {\n");
    fwrite($fp, '    $chains = array();' . "\n\n");

    foreach ($chain as $parent => $children) {
      fwrite($fp, '    $chains[' . "'{$parent}'] = array(");
      $set = false;
      foreach ($children as $child) {
        if ($set) {
          fwrite($fp, ",'{$child}'");
        } else {
          fwrite($fp, "'{$child}'");
          $set = true;
        }
      }
      fwrite($fp, ");\n");
    }

    fwrite($fp, "\n");
    fwrite($fp, '    return $chains;' . "\n");
    fwrite($fp, "  }\n");
    fwrite($fp, "}");
    fclose($fp);
  }

  private static function selectConnectName($tName, $connectName)
  {
    if (in_array($tName, TableList_Writer::get($connectName))) {
      return $connectName . ':' . $tName;
    } else {
      foreach (Schema_Generator::$connectNameList as $connectName) {
        if (in_array($tName, TableList_Writer::get($connectName))) {
          return $connectName . ':' . $tName;
        }
      }
    }
  }
}

class TableList_Writer
{
  private static $tableList = array();

  public static function add($connectName, $tName)
  {
    self::$tableList[$connectName][] = $tName;
  }

  public static function get($connectName)
  {
    return self::$tableList[$connectName];
  }

  public static function write($connectName, $dirPath)
  {
    $className = ucfirst($connectName) . '_TableList';
    $target = "{$dirPath}/{$className}.php";
    echo "generate Table List: {$connectName} \n";
    $fp = fopen($target, 'w');

    fwrite($fp, "<?php\n\n");
    fwrite($fp, "class {$className}\n");
    fwrite($fp, "{\n");
    fwrite($fp, "  public function get()\n");
    fwrite($fp, "  {\n");
    fwrite($fp, '    $list = array(');

    $tableList = self::$tableList[$connectName];
    $table     = $tableList[0];
    fwrite($fp, "'$table'");

    for ($i = 1; $i < count($tableList); $i++) {
      $table = self::$tableList[$i];
      fwrite($fp, ",'{$table}'");
    }

    fwrite($fp, ");\n\n");
    fwrite($fp, '    return $list;' . "\n");
    fwrite($fp, "  }\n");
    fwrite($fp, "}");
    fclose($fp);
  }
}

class ParsedSQL_Writer
{
  public static function write($connectName, $tName, $colArray, $dirPath)
  {
    $className = ucfirst($connectName) . '_' . ucfirst($tName);
    $target = "{$dirPath}/{$className}.php";
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
  public static function make($connectName, $schema)
  {
    $parsed  = array();
    $columns = $schema->getColumns();

    foreach ($columns as $column) {
      if (strpos($column->name, '_id') !== false) {
        Cascade_Writer::addForeignKey($connectName, $schema->getTableName(), $column->name);
      }

      $info = array();
      array_push($info, '$sql[' . "'{$column->name}'] = array(");
      array_push($info, "'type' => '{$column->type}', ");

      if ($column->type === Sabel_DB_Const::INT) {
        array_push($info, "'max' => {$column->max}, ");
        array_push($info, "'min' => {$column->min}, ");
      } else if ($column->type === Sabel_DB_Const::STRING) {
        array_push($info, "'max' => {$column->max}, ");
      }

      $increment = ($column->increment) ? 'true' : 'false';
      $notNull   = ($column->notNull) ? 'true' : 'false';
      $primary   = ($column->primary) ? 'true' : 'false';

      array_push($info, "'increment' => {$increment}, ");
      array_push($info, "'notNull' => {$notNull}, ");
      array_push($info, "'primary' => {$primary}, ");

      $def = $column->default;
      if (is_null($def)) {
        array_push($info, "'default' => null");
      } else if (is_numeric($def)) {
        array_push($info, "'default' => {$def}");
      } else if (is_bool($def)) {
        $def = ($def) ? 'true' : 'false';
        array_push($info, "'default' => {$def}");
      } else {
        array_push($info, "'default' => '{$def}'");
      }

      array_push($info, ");\n");
      $parsed[$column->name] = join('', $info);
    }
    return $parsed;
  }
}

class Schema_Generator
{
  public static $connectNameList = array();

  public static function main()
  {
    $yml  = new Sabel_Config_Yaml('database.yml');
    $data = $yml->read($_SERVER['argv'][1]);

    foreach ($data as $connectName => $params) {
      self::$connectNameList[] = $connectName;
      Sabel_DB_Connection::addConnection($connectName, $params);

      $sa = new Sabel_DB_Schema_Accessor($connectName, $params['schema']);
      $schemas = $sa->getTables();

      foreach ($schemas as $schema) {
        $tName    = $schema->getTableName();
        $colArray = ParsedSQL_Maker::make($connectName, $schema);
        ParsedSQL_Writer::write($connectName, $tName, $colArray, $_SERVER['argv'][2]);
        TableList_Writer::add($connectName, $tName);
      }
      TableList_Writer::write($connectName, $_SERVER['argv'][2]);
    }

    Cascade_Writer::write($_SERVER['argv'][2]);
  }
}
/*
if (count($_SERVER['argv']) === 1) {
  echo "usage: php Generator.php [environment] [dirpath]\n\n";
  exit;
}
*/

//Schema_Generator::main();
