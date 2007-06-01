<?php

if (!defined("RUN_BASE")) define("RUN_BASE", getcwd());
define("SCHEMA_DIR", "lib/schema/");

Sabel::fileUsing("config/environment.php");

class Schema
{
  public function run()
  {
    $input = $this->getInputs();
    clearstatcache();

    $environment  = environment(strtolower($input[2]));
    $inputSchemas = $this->getWriteSchemas($input);
    $schemaWrite  = (!empty($inputSchemas));

    if (isset($inputSchemas[0]) && $inputSchemas[0] === "all") {
      $schemaAll = (count($inputSchemas) === 1);
    } else {
      $schemaAll = false;
    }

    foreach (get_db_params($environment) as $connectionName => $params) {
      Sabel_DB_Config::regist($connectionName, $params);
      $accessor = new Sabel_DB_Schema_Accessor($connectionName);

      foreach ($accessor->getAll() as $schema) {
        $tblName  = $schema->getTableName();
        $colArray = Schema_Creator::make($schema);

        if ($schemaAll || $schemaWrite && in_array($tblName, $inputSchemas)) {
          Schema_Writer::write($tblName, $colArray, $accessor, $params["driver"]);
        }

        TableList_Writer::add($connectionName, $tblName);
      }

      if (in_array("-l", $input)) TableList_Writer::write($connectionName);
    }
  }

  private function getWriteSchemas($input)
  {
    $inputSchemas = array();

    if (in_array("-s", $input)) {
      $key = array_search("-s", $input) + 1;
      for ($i = $key; $i < count($input); $i++) {
        $val = $input[$i];
        if ($val === "-l" || $input[$i] === "-c") break;
        $inputSchemas[] = $val;
      }
    }

    return $inputSchemas;
  }

  private function getInputs()
  {
    $input = $_SERVER["argv"];
    if (count($input) < 3) {
      sakle_schema_help(); exit;
    }

    return $input;
  }
}

class TableList_Writer
{
  private static $tableList = array();

  public static function add($connectionName, $tblName)
  {
    self::$tableList[$connectionName][] = $tblName;
  }

  public static function get($connectionName)
  {
    return self::$tableList[$connectionName];
  }

  public static function write($connectionName)
  {
    $cn        = $connectionName;
    $fileName  = ucfirst($cn) . "TableList";
    $target    = SCHEMA_DIR . "{$fileName}.php";
    $className = "Schema_" . $fileName;

    echo "generate Table List: $cn \n\n";

    $fp = fopen($target, "w");

    fwrite($fp, "<?php\n\n");
    fwrite($fp, "class {$className}\n{\n");
    fwrite($fp, "  public function get()\n  {\n");
    fwrite($fp, "    return array(");

    $tableList = self::$tableList[$cn];
    fwrite($fp, '"' . $tableList[0] . '"');

    for ($i = 1; $i < count($tableList); $i++) {
      fwrite($fp, ', "' . $tableList[$i] . '"');
    }

    fwrite($fp, ");\n  }\n}\n");
    fclose($fp);
  }
}

class Schema_Writer
{
  public static function write($tblName, $colArray, $sa, $drvName)
  {
    $mdlName   = convert_to_modelname($tblName);
    $className = "Schema_" . $mdlName;
    $target    = SCHEMA_DIR . $mdlName . ".php";

    echo "generate Schema {$target} \n";

    // @todo
    if (file_exists($target)) return null;

    $fp = fopen($target, "w");

    fwrite($fp, "<?php\n\n");
    fwrite($fp, "class {$className}\n{\n");
    fwrite($fp, "  public static function get()\n  {\n");
    fwrite($fp, '    $cols = array();');
    fwrite($fp, "\n\n");

    foreach ($colArray as $line) fwrite($fp, "    " . $line);

    fwrite($fp, "\n    return " . '$cols;' . "\n  }\n");

    $property   = array();
    $property[] = '$property = array(';
    self::writePrimary($property, $tblName);

    $property[] = "                      ";
    self::writeIncrement($property, $tblName);

    $property[] = "                      ";
    // @todo
    self::writeEngine($property, $tblName, $sa, $drvName);

    fwrite($fp, "\n  public function getProperty()\n  {\n");
    fwrite($fp, "    " . join("", $property));
    fwrite($fp, "    " . 'return $property;' . "\n  }\n}\n");
    fclose($fp);
  }

  private static function writePrimary(&$property, $tblName)
  {
    if (isset(Schema_Creator::$tblPrimary[$tblName])) {
      $pkey = Schema_Creator::$tblPrimary[$tblName];

      if (count($pkey) === 1) {
        $property[] = "'primaryKey'   => '{$pkey[0]}',\n";
      } else {
        $keys = array();
        foreach ($pkey as $key) $keys[] = '"' . $key . '"';
        $property[] = "'primaryKey'   => array(" . join(", ", $keys) . "),\n";
      }
    } else {
      $property[] = "'primaryKey'   => null,\n";
    }
  }

  private static function writeIncrement(&$property, $tblName)
  {
    if (isset(Schema_Creator::$tblIncrement[$tblName])) {
      $iKey = Schema_Creator::$tblIncrement[$tblName];
      $property[] = "'incrementKey' => '{$iKey}',\n";
    } else {
      $property[] = "'incrementKey' => null,\n";
    }
  }

  // @todo
  private static function writeEngine(&$property, $tblName, $accessor, $drvName)
  {
    if ($drvName === "mysql" || $drvName === "pdo-mysql") {
      $engine = $accessor->getTableEngine($tblName);
      $property[] = "'tableEngine'  => '{$engine}');\n\n";
    } else {
      $property[] = "'tableEngine'  => null);\n\n";
    }
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

    foreach ($columns as $col) {
      $info  = array();
      $isNum = false;

      $info[] = '$cols[' . "'{$col->name}'] = array(";
      $info[] = "'type' => Sabel_DB_Type::{$col->type}, ";

      if ($col->isInt() || $col->isFloat() || $col->isDouble()) {
        $info[] = "'max' => {$col->max}, ";
        $info[] = "'min' => {$col->min}, ";
        $isNum = true;
      } elseif ($col->isString()) {
        $info[] = "'max' => {$col->max}, ";
      }

      self::setConstraints($info, $col);

      if ($col->primary)   self::$tblPrimary[$tName][] = $col->name;
      if ($col->increment) self::$tblIncrement[$tName] = $col->name;

      $info[] = "'default' => " . self::getDefault($isNum, $col);
      $parsed[$col->name] = join("", $info) . ");\n";
    }

    return $parsed;
  }

  private static function setConstraints(&$info, $column)
  {
    $increment = ($column->increment) ? "true" : "false";
    $nullable  = ($column->nullable)  ? "true" : "false";
    $primary   = ($column->primary)   ? "true" : "false";

    $info[] = "'increment' => {$increment}, ";
    $info[] = "'nullable' => {$nullable}, ";
    $info[] = "'primary' => {$primary}, ";
  }

  private static function getDefault($isNum, $column)
  {
    $default = $column->default;

    if ($default === null) {
      $str = "null";
    } elseif ($isNum) {
      $str = $default;
    } elseif ($column->isBool()) {
      $str = ($default) ? "true" : "false";
    } else {
      $str = "'" . $default . "'";
    }

    return $str;
  }
}

function sakle_schema_help()
{
  echo "Usage: sakle Schema [environment]\n";
  echo "       -l  create table list\n";
  echo "       -s  create schema : table_name1, table_name2... , or all\n";
  exit;
}
