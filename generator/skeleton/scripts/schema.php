<?php

define('SABEL',      '/Users/morireo/MirrorSandbox/sabel/trunk/sabel/');
define('SABEL_DB',   SABEL . 'db/');
define('SCHEMA_DIR', 'schema/');

require_once SABEL_DB . 'Connection.php';

require_once SABEL_DB . 'driver/ResultSet.php';
require_once SABEL_DB . 'driver/ResultObject.php';

require_once SABEL_DB . 'driver/Statement.php';
require_once SABEL_DB . 'driver/native/Paginate.php';
require_once SABEL_DB . 'driver/native/Query.php';
require_once SABEL_DB . 'driver/pdo/Query.php';

require_once SABEL_DB . 'driver/General.php';
require_once SABEL_DB . 'driver/native/Mysql.php';
require_once SABEL_DB . 'driver/native/Pgsql.php';
require_once SABEL_DB . 'driver/native/Mssql.php';
require_once SABEL_DB . 'driver/native/Firebird.php';
require_once SABEL_DB . 'driver/pdo/Statement.php';
require_once SABEL_DB . 'driver/pdo/Driver.php';

require_once SABEL_DB . 'SimpleCache.php';
require_once SABEL_DB . 'Transaction.php';
require_once SABEL_DB . 'Mapper.php';
require_once SABEL_DB . 'Basic.php';
require_once SABEL_DB . 'Tree.php';
require_once SABEL_DB . 'Bridge.php';

require_once SABEL_DB . 'schema/Const.php';

require_once SABEL_DB . 'schema/type/Sender.php';
require_once SABEL_DB . 'schema/type/Setter.php';

require_once SABEL_DB . 'schema/type/Int.php';
require_once SABEL_DB . 'schema/type/String.php';
require_once SABEL_DB . 'schema/type/Byte.php';
require_once SABEL_DB . 'schema/type/Other.php';
require_once SABEL_DB . 'schema/type/Text.php';
require_once SABEL_DB . 'schema/type/Time.php';
require_once SABEL_DB . 'schema/type/Float.php';
require_once SABEL_DB . 'schema/type/Double.php';

require_once SABEL_DB . 'schema/Column.php';
require_once SABEL_DB . 'schema/Table.php';

require_once SABEL_DB . 'schema/Common.php';
require_once SABEL_DB . 'schema/General.php';
require_once SABEL_DB . 'schema/Mysql.php';
require_once SABEL_DB . 'schema/Pgsql.php';
require_once SABEL_DB . 'schema/Sqlite.php';
require_once SABEL_DB . 'schema/Mssql.php';
require_once SABEL_DB . 'schema/Accessor.php';

require_once SABEL . 'config/Spyc.php';
require_once SABEL . 'config/Yaml.php';

class Cascade_Writer
{
  private static $references  = array();
  private static $foreignKeys = array();

  public static function addForeignKey($connectName, $table, $key)
  {
    self::$foreignKeys[$connectName][$table][] = $key;
  }

  public static function write()
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
        $tName = self::selectConnectName($tName);
        foreach ($children as $child) {
          $child = self::selectConnectName($child);
          $chain[$tName][] = $child;
        }
      }
    }

    $target = SCHEMA_DIR . 'Schema_CascadeChain.php';
    echo "generate Cascade Chain\n\n";
    $fp = fopen($target, 'w');

    fwrite($fp, "<?php\n\n");
    fwrite($fp, "class Schema_CascadeChain\n{\n");
    fwrite($fp, "  public function get()\n  {\n");
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
    fwrite($fp, '    return $chains;' . "\n  }\n}");
    fclose($fp);
  }

  private static function selectConnectName($tName)
  {
    foreach (Schema_Util_Generator::$connectNameList as $connectName) {
      if (in_array($tName, TableList_Writer::get($connectName))) return $connectName . ':' . $tName;
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

  public static function write($connectName)
  {
    $className = 'Schema_' . ucfirst($connectName) . 'TableList';
    $target = SCHEMA_DIR . "{$className}.php";
    echo "generate Table List: {$connectName}\n\n";
    $fp = fopen($target, 'w');

    fwrite($fp, "<?php\n\n");
    fwrite($fp, "class {$className}\n{\n");
    fwrite($fp, "  public function get()\n  {\n");
    fwrite($fp, '    $list = array(');

    $tableList = self::$tableList[$connectName];
    $table     = $tableList[0];
    fwrite($fp, "'$table'");

    for ($i = 1; $i < count($tableList); $i++) {
      $table = $tableList[$i];
      fwrite($fp, ",'{$table}'");
    }

    fwrite($fp, ");\n\n");
    fwrite($fp, '    return $list;' . "\n  }\n}");
    fclose($fp);
  }
}

class Schema_Writer
{
  public static function write($tName, $colArray, $connectName, $sa, $drvName)
  {
    $className = 'Schema_' . join('', array_map('ucfirst', explode('_', $tName)));

    $target = SCHEMA_DIR . "{$className}.php";
    echo "generate Schema {$target} \n";
    $fp = fopen($target, 'w');

    fwrite($fp, "<?php\n\n");
    fwrite($fp, "class {$className}\n{\n");
    fwrite($fp, "  public function get()\n  {\n");
    fwrite($fp, '    $sql = array();');
    fwrite($fp, "\n\n");

    foreach ($colArray as $line) fwrite($fp, '    ' . $line);

    fwrite($fp, "\n    return " . '$sql;' . "\n  }\n");

    fwrite($fp, "\n  public function getConnectName()\n  {\n");
    fwrite($fp, "    return '{$connectName}';\n  }\n");

    fwrite($fp, "\n  public function getParents()\n  {\n");
    if (array_key_exists($tName, Schema_Maker::$tblParents)) {
      $parents = Schema_Maker::$tblParents[$tName];
      fwrite($fp, '    return array(');
      $pArray = array();
      array_push($pArray, "'{$parents[0]}'");
      for ($i = 1; $i < sizeof($parents); $i++) array_push($pArray, ", '{$parents[$i]}'");
      fwrite($fp, join('', $pArray));
      fwrite($fp, ");\n  }\n");
    } else {
      fwrite($fp, "    return null;\n  }\n");
    }

    fwrite($fp, "\n  public function getPrimaryKey()\n  {\n");
    if (array_key_exists($tName, Schema_Maker::$tblPrimary)) {
      $pArray = Schema_Maker::$tblPrimary[$tName];
      if (sizeof($pArray) === 1) {
        fwrite($fp, "    return '{$pArray[0]}';\n  }\n");
      } else {
        $keys = array();
        fwrite($fp, '    return array(');
        array_push($keys, "'{$pArray[0]}'");
        for ($i = 1; $i < sizeof($pArray); $i++) array_push($keys, ", '{$pArray[$i]}'");
        fwrite($fp, join('', $keys));
        fwrite($fp, ");\n  }\n");
      }
    } else {
      fwrite($fp, "    return null;\n  }\n");
    }

    if ($drvName === 'mysql' || $drvName === 'pdo-mysql') {
      $engine = $sa->getTableEngine($tName);
      fwrite($fp, "\n  public function getEngine()\n  {\n");
      fwrite($fp, "    return '{$engine}';\n  }\n");
    }

    fwrite($fp,  "}\n");
    fclose($fp);
  }
}

class Schema_Maker
{
  public static $tblParents = array();
  public static $tblPrimary = array();

  public static function make($connectName, $schema)
  {
    $parsed  = array();
    $tName   = $schema->getTableName();
    $columns = $schema->getColumns();

    foreach ($columns as $column) {
      if (strpos($column->name, '_id') !== false) {
        self::$tblParents[$tName][] = str_replace('_id', '', $column->name);
        Cascade_Writer::addForeignKey($connectName, $tName, $column->name);
      }

      $info = array();
      array_push($info, '$sql[' . "'{$column->name}'] = array(");
      array_push($info, "'type' => '{$column->type}', ");

      if ($column->type === Sabel_DB_Schema_Const::INT) {
        array_push($info, "'max' => {$column->max}, ");
        array_push($info, "'min' => {$column->min}, ");
      } else if ($column->type === Sabel_DB_Schema_Const::STRING) {
        array_push($info, "'max' => {$column->max}, ");
      }

      $increment = ($column->increment) ? 'true' : 'false';
      $nullable  = ($column->nullable)  ? 'true' : 'false';
      $primary   = ($column->primary)   ? 'true' : 'false';

      array_push($info, "'increment' => {$increment}, ");
      array_push($info, "'nullable' => {$nullable}, ");
      array_push($info, "'primary' => {$primary}, ");

      if ($column->primary) self::$tblPrimary[$tName][] = $column->name;

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

class Schema_Util_Generator
{
  public static $connectNameList = array();

  public static function main()
  {
    $input = $_SERVER['argv'];

    $yml   = new Sabel_Config_Yaml(SABEL_DB . 'schema/util/database.yml');
    $data  = $yml->read($input[1]);

    $schemaWrite  = false;
    $schemaAll    = false;
    $inputSchemas = array();

    if (in_array('-s', $input)) {
      $schemaWrite = true;
      $key = array_search('-s', $input) + 1;
      for ($i = $key; $i < count($input); $i++) {
        $val = $input[$i];
        if ($val === '-l' || $input[$i] === '-c') break;
        $inputSchemas[] = $val;
      }
      $schemaAll = (count($inputSchemas) === 1 && $inputSchemas[0] === 'all');
    }

    foreach ($data as $connectName => $params) {
      self::$connectNameList[] = $connectName;
      Sabel_DB_Connection::addConnection($connectName, $params);

      $sa = new Sabel_DB_Schema_Accessor($connectName, $params['schema']);
      $schemas = $sa->getTables();

      foreach ($schemas as $schema) {
        $tName    = $schema->getTableName();
        $colArray = Schema_Maker::make($connectName, $schema);
        if ($schemaAll || $schemaWrite && in_array($tName, $inputSchemas)) {
          Schema_Writer::write($tName, $colArray, $connectName, $sa, $params['driver']);
        }
        TableList_Writer::add($connectName, $tName);
      }
      if (in_array('-l', $input)) TableList_Writer::write($connectName);
    }
    if (in_array('-c', $input)) Cascade_Writer::write();
  }
}

if (count($_SERVER['argv']) === 1) {
  echo "usage: php Generator.php environment\n";
  echo "       [-c] make cascade chain\n";
  echo "       [-l] make table list\n";
  echo "       [-s] make schema : table name1, name2... , or all\n";
  exit;
}

Schema_Util_Generator::main();
