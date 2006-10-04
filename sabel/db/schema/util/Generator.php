<?php
/*
define('SABEL_DB',   '/usr/local/www/data/trunk/Sabel/sabel/db/');
define('MODELS_DIR', '/usr/local/www/data/trunk/Sabel/sabel/db/schema/util/models/');
define('SCHEMA_DIR', '/usr/local/www/data/trunk/Sabel/sabel/db/schema/util/schema/');

require_once SABEL_DB . 'Connection.php';

require_once SABEL_DB . 'driver/Query.php';
require_once SABEL_DB . 'driver/native/Query.php';
require_once SABEL_DB . 'driver/pdo/Query.php';

require_once SABEL_DB . 'driver/General.php';
require_once SABEL_DB . 'driver/native/Mysql.php';
require_once SABEL_DB . 'driver/native/Pgsql.php';
require_once SABEL_DB . 'driver/pdo/Driver.php';

require_once SABEL_DB . 'Const.php';
require_once SABEL_DB . 'Transaction.php';
require_once SABEL_DB . 'SimpleCache.php';
require_once SABEL_DB . 'Mapper.php';
require_once SABEL_DB . 'BaseClasses.php';

require_once SABEL_DB . 'schema/Types.php';
require_once SABEL_DB . 'schema/Setter.php';
require_once SABEL_DB . 'schema/Column.php';
require_once SABEL_DB . 'schema/Table.php';
require_once SABEL_DB . 'schema/General.php';
require_once SABEL_DB . 'schema/MyPg.php';
require_once SABEL_DB . 'schema/Mysql.php';
require_once SABEL_DB . 'schema/Pgsql.php';
require_once SABEL_DB . 'schema/Accessor.php';

require_once '/usr/local/www/data/trunk/Sabel/sabel/config/Spyc.php';
require_once '/usr/local/www/data/trunk/Sabel/sabel/config/Yaml.php';
*/

class ModelClass_Writer
{
  private static $models     = array();
  private static $tIncrement = array();
  private static $tPrimary   = array();

  public static function add($connectName, $tName)
  {
    self::$models[$connectName][] = $tName;
  }

  public static function addInc($connectName, $tName, $increment)
  {
    self::$tIncrement[$connectName][$tName] = $increment;
  }

  public static function addPri($connectName, $tName, $primary)
  {
    self::$tPrimary[$connectName][$tName] = $primary;
  }

  public static function write($inputTables = null)
  {
    foreach (self::$models as $connectName => $tArray) {
      foreach (array_values($tArray) as $table) {
        if (!empty($inputTables) && (count($inputTables) !== 1 || $inputTables[0] !== 'all')) {
          if (!in_array($table, $inputTables)) continue;
        }

        $underBar = false;
        if (strpos($table, '_') !== false) {
          $underBar  = true;
          $class = join('', array_map('ucfirst', explode('_', $table)));
        } else {
          $class = ucfirst($table);
        }

        $target = MODELS_DIR . "{$class}.php";

        echo "generate Model {$target}\n";

        $fp = fopen($target, 'w');
        fwrite($fp, "<?php\n\n");
        fwrite($fp, "class {$class} extends Sabel_DB_Mapper\n{\n");

        $flag = false;

        if ($underBar) {
          fwrite($fp, '  protected $table = ' . "'{$table}';\n");
          $flag = true;
        }

        if (!self::$tIncrement[$connectName][$table]) {
          fwrite($fp, '  protected $autoNumber = false;' . "\n");
          $flag = true;
        }

        $primary = (isset(self::$tPrimary[$connectName])) ? self::$tPrimary[$connectName] : null;

        if (is_array($primary) && array_key_exists($table, $primary)) {
          $pri  = $primary[$table];
          $line = '  protected $jointKey = array(' . "'{$pri[0]}'";

          for ($i = 1; $i < count($pri); $i++) {
            $line .= ", '{$pri[$i]}'";
          }

          fwrite($fp, $line . ");\n");
          $flag = true;
        }

        if ($flag) fwrite($fp, "\n");

        fwrite($fp, '  public function __construct($param1 = null, $param2 = null)');
        fwrite($fp, "\n  {\n");
        fwrite($fp, '    $this->setDriver(' . "'{$connectName}');\n");
        fwrite($fp, '    parent::__construct($param1, $param2);' . "\n");
        fwrite($fp, "  }\n}");
        fclose($fp);
      }
    }
  }
}

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

    $target = SCHEMA_DIR . 'Cascade_Chain.php';
    echo "generate Cascade Chain\n\n";
    $fp = fopen($target, 'w');

    fwrite($fp, "<?php\n\n");
    fwrite($fp, "class Cascade_Chain\n{\n");
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
    $className = ucfirst($connectName) . '_TableList';
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
  public static function write($tName, $colArray)
  {
    $className = 'Schema_' . join('', array_map('ucfirst', explode('_', $tName)));

    $target = SCHEMA_DIR . "{$className}.php";
    echo "generate Schema {$target} \n";
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

class Schema_Maker
{
  public static function make($connectName, $schema)
  {
    $parsed  = array();
    $tName   = $schema->getTableName();
    $columns = $schema->getColumns();

    ModelClass_Writer::add($connectName, $tName);
    $tIncrement = false;
    $tPrimary   = array();

    foreach ($columns as $column) {
      if (strpos($column->name, '_id') !== false) {
        Cascade_Writer::addForeignKey($connectName, $tName, $column->name);
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

      if (!$tIncrement && $column->increment) $tIncrement = true;
      if ($column->primary) $tPrimary[] = $column->name;

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

    ModelClass_Writer::addInc($connectName, $tName, $tIncrement);
    if (count($tPrimary) > 1) {
      ModelClass_Writer::addPri($connectName, $tName, $tPrimary);
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

    $yml   = new Sabel_Config_Yaml('database.yml');
    $data  = $yml->read($input[1]);

    $schemaWrite  = false;
    $schemaAll    = false;
    $inputSchemas = array();

    if (in_array('-s', $input)) {
      $schemaWrite = true;
      $key = array_search('-s', $input) + 1;
      for ($i = $key; $i < count($input); $i++) {
        $val = $input[$i];
        if ($val === '-m' || $val === '-l' || $input[$i] === '-c') break;
        $inputSchemas[] = $val;
      }

      if (count($inputSchemas) === 1 && $inputSchemas[0] === 'all') {
        $schemaAll = true;
      }
    }

    $modelWrite  = false;
    $inputModels = array();

    if (in_array('-m', $input)) {
      $modelWrite = true;
      $key = array_search('-m', $input) + 1;
      for ($i = $key; $i < count($input); $i++) {
        $val = $input[$i];
        if ($val === '-s' || $val === '-l' || $input[$i] === '-c') break;
        $inputModels[] = $val;
      }
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
          Schema_Writer::write($tName, $colArray);
        }
        TableList_Writer::add($connectName, $tName);
      }

      if (in_array('-l', $input)) TableList_Writer::write($connectName);
    }

    if (in_array('-c', $input)) Cascade_Writer::write();
    if ($modelWrite) ModelClass_Writer::write($inputModels);
  }
}

/*
if (count($_SERVER['argv']) === 1) {
  echo "usage: php Generator.php environment\n";
  echo "       [-c] make cascade chain\n";
  echo "       [-l] make table list\n";
  echo "       [-s] make schema : table name1, name2... , or all\n";
  echo "       [-m] make models : table name1, name2... , or all\n";
  exit;
}

Schema_Util_Generator::main();
*/
