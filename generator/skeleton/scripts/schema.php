<?php

define('SCHEMA_DIR', 'lib/schema/');
//define('SABEL', '/usr/.../.../Sabel/');

if (!defined('SABEL')) {
  trigger_error('you must define SABEL directory before run', E_USER_ERROR);
}

define ('RUN_BASE', getcwd());

require SABEL . 'Sabel.php';
require RUN_BASE . '/config/environment.php';

set_include_path(SABEL . ":" . get_include_path());

Sabel::using('Sabel_DB_Connection');
Sabel::using('Sabel_DB_Executer');
Sabel::using('Sabel_DB_Schema_Accessor');
Sabel::using('Sabel_DB_Type_Const');
Sabel::using('Sabel_DB_Type_Setter');

class Cascade_Writer
{
  private static $foreignKeys = array();

  public static function addForeignKey($table, $key)
  {
    self::$foreignKeys[$table][] = $key;
  }

  public static function write()
  {
    $chain = array();

    foreach (self::$foreignKeys as $tblName => $colNames) {
      foreach ($colNames as $colName) {
        $parent = str_replace('_id', '', $colName);
        $chain[$parent][] = $tblName;
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
  public static function write($tName, $colArray, $sa, $drvName)
  {
    $className = 'Schema_' . join('', array_map('ucfirst', explode('_', $tName)));

    $target = SCHEMA_DIR . join('', array_map('ucfirst', explode('_', $tName))).".php";
    echo "generate Schema {$target} \n";
    $fp = fopen($target, 'w');

    fwrite($fp, "<?php\n\n");
    fwrite($fp, "class {$className}\n{\n");
    fwrite($fp, "  public static function get()\n  {\n");
    fwrite($fp, '    $cols = array();');
    fwrite($fp, "\n\n");

    foreach ($colArray as $line) fwrite($fp, '    ' . $line);

    fwrite($fp, "\n    return " . '$cols;' . "\n  }\n");

    $property = array();
    array_push($property, '$property = array(');
    if (array_key_exists($tName, Schema_Creator::$tblPrimary)) {
      $pArray = Schema_Creator::$tblPrimary[$tName];
      if (sizeof($pArray) === 1) {
        array_push($property, "'primaryKey'   => '{$pArray[0]}',\n");
      } else {
        $keys = array();
        array_push($keys, "'{$pArray[0]}'");
        for ($i = 1; $i < sizeof($pArray); $i++) array_push($keys, ", '{$pArray[$i]}'");
        array_push($property, "'primaryKey'   => array(" . join('', $keys) . "),\n");
      }
    } else {
      array_push($property, "'primaryKey'   => null,\n");
    }

    array_push($property, '                      ');
    if (array_key_exists($tName, Schema_Creator::$tblIncrement)) {
      $iKey = Schema_Creator::$tblIncrement[$tName];
      array_push($property, "'incrementKey' => '{$iKey}',\n");
    } else {
      array_push($property, "'incrementKey' => null,\n");
    }

    array_push($property, '                      ');
    if ($drvName === 'mysql' || $drvName === 'pdo-mysql') {
      $engine = $sa->getTableEngine($tName);
      array_push($property, "'tableEngine'  => '{$engine}');\n\n");
    } else {
      array_push($property, "'tableEngine'  => null);\n\n");
    }

    fwrite($fp, "\n  public function getProperty()\n  {\n");
    fwrite($fp, '    ' . join('', $property));
    fwrite($fp, "    " . 'return $property;');
    fwrite($fp, "\n  }\n}\n");
    fclose($fp);
  }
}

class Schema_Creator
{
  public static $tblPrimary   = array();
  public static $tblIncrement = array();

  public static function make($schema)
  {
    $parsed  = array();
    $tName   = $schema->getTableName();
    $columns = $schema->getColumns();

    foreach ($columns as $column) {
      if (strpos($column->name, '_id') !== false) {
        Cascade_Writer::addForeignKey($tName, $column->name);
      }

      $info = array();
      array_push($info, '$cols[' . "'{$column->name}'] = array(");
      array_push($info, "'type' => '{$column->type}', ");

      if ($column->type === Sabel_DB_Type_Const::INT) {
        array_push($info, "'max' => {$column->max}, ");
        array_push($info, "'min' => {$column->min}, ");
      } elseif ($column->type === Sabel_DB_Type_Const::STRING) {
        array_push($info, "'max' => {$column->max}, ");
      }

      $increment = ($column->increment) ? 'true' : 'false';
      $nullable  = ($column->nullable)  ? 'true' : 'false';
      $primary   = ($column->primary)   ? 'true' : 'false';

      array_push($info, "'increment' => {$increment}, ");
      array_push($info, "'nullable' => {$nullable}, ");
      array_push($info, "'primary' => {$primary}, ");

      if ($column->primary)   self::$tblPrimary[$tName][] = $column->name;
      if ($column->increment) self::$tblIncrement[$tName] = $column->name;

      $def = $column->default;
      if (is_null($def)) {
        array_push($info, "'default' => null");
      } elseif (is_numeric($def)) {
        array_push($info, "'default' => {$def}");
      } elseif (is_bool($def)) {
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
  public static function main()
  {
    $input = $_SERVER['argv'];
    Sabel::fileUsing(getcwd() . '/config/database.php');

    $environment = $input[1];
    switch ($environment) {
      case 'production':
        $environment = PRODUCTION;
        break;
      case 'test':
        $environment = TEST;
        break;
      case 'development':
        $environment = DEVELOPMENT;
        break;
    }

    $data = get_db_params($environment);

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
      Sabel_DB_Connection::addConnection($connectName, $params);

      $sa = ($params['driver'] === 'pdo-sqlite')
          ? new Sabel_DB_Schema_Accessor($connectName)
          : new Sabel_DB_Schema_Accessor($connectName, $params['schema']);

      foreach ($sa->getTables() as $schema) {
        $tName    = $schema->getTableName();
        $colArray = Schema_Creator::make($schema);
        if ($schemaAll || $schemaWrite && in_array($tName, $inputSchemas)) {
          Schema_Writer::write($tName, $colArray, $sa, $params['driver']);
        }
        TableList_Writer::add($connectName, $tName);
      }
      if (in_array('-l', $input)) TableList_Writer::write($connectName);
    }
    if (in_array('-c', $input)) Cascade_Writer::write();
  }
}

if (count($_SERVER['argv']) === 1) {
  echo "Usage: php schema.php [environment]\n";
  echo "       -c  create cascade chain\n";
  echo "       -l  create table list\n";
  echo "       -s  create schema : table_name1, table_name2... , or all\n";
  exit;
}

Schema_Generator::main();
