<?php

if (!defined("RUN_BASE")) define("RUN_BASE", getcwd());
define("SCHEMA_DIR", "lib" . DIR_DIVIDER . "schema" . DIR_DIVIDER);

Sabel::fileUsing("config" . DIR_DIVIDER . "environment.php");

/**
 * Schema
 *
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Schema extends Sabel_Sakle_Task
{
  public function run($arguments)
  {
    $this->checkInputs($arguments);
    
    clearstatcache();
    
    $environment  = environment(strtolower($arguments[2]));
    $inputSchemas = $this->getWriteSchemas($arguments);
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
        $tblName = $schema->getTableName();
        
        if ($schemaAll || $schemaWrite && in_array($tblName, $inputSchemas)) {
          $colLines = $this->createColumnLines($schema);
          $target   = SCHEMA_DIR . convert_to_modelname($tblName) . ".php";
          $this->printMessage("generate Schema $target");
          
          Schema_Writer::write($colLines, $schema);
        }
        
        TableList_Writer::add($connectionName, $tblName);
      }
      
      if (in_array("-l", $arguments)) TableList_Writer::write($connectionName);
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

  private function createColumnLines($schema)
  {
    $lines   = array();
    $tName   = $schema->getTableName();
    $columns = $schema->getColumns();
    
    foreach ($columns as $col) {
      $line  = array();
      $isNum = false;
      
      $line[] = '$cols[' . "'{$col->name}'] = array(";
      
      $type   = str_replace("_", "", $col->type);
      $line[] = "'type' => Sabel_DB_Type::{$type}, ";
      
      if ($col->isInt() || $col->isFloat() || $col->isDouble()) {
        $line[] = "'max' => {$col->max}, ";
        $line[] = "'min' => {$col->min}, ";
        $isNum = true;
      } elseif ($col->isString()) {
        $line[] = "'max' => {$col->max}, ";
      }
      
      $this->setConstraints($line, $col);
      
      $line[] = "'default' => " . $this->getDefault($isNum, $col);
      $lines[$col->name] = join("", $line) . ");\n";
    }
    
    return $lines;
  }
  
  private function setConstraints(&$line, $column)
  {
    $increment = ($column->increment) ? "true" : "false";
    $nullable  = ($column->nullable)  ? "true" : "false";
    $primary   = ($column->primary)   ? "true" : "false";
    
    $line[] = "'increment' => {$increment}, ";
    $line[] = "'nullable' => {$nullable}, ";
    $line[] = "'primary' => {$primary}, ";
  }
  
  private function getDefault($isNum, $column)
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
  
  private function checkInputs($arguments)
  {
    if (count($arguments) < 3) {
      sakle_schema_help(); exit;
    } elseif ($arguments[2] === "--help" || $arguments[2] === "-h") {
      sakle_schema_help(); exit;
    }
  }
}

class Schema_Writer
{
  public static function write($colLines, $schema)
  {
    $mdlName   = convert_to_modelname($schema->getTableName());
    $className = "Schema_" . $mdlName;
    $target    = SCHEMA_DIR . $mdlName . ".php";
    
    if (file_exists($target)) unlink($target);
    
    $fp = fopen($target, "w");
    
    fwrite($fp, "<?php\n\n");
    fwrite($fp, "class {$className}\n{\n");
    fwrite($fp, "  public static function get()\n  {\n");
    fwrite($fp, '    $cols = array();');
    fwrite($fp, "\n\n");
    
    foreach ($colLines as $line) fwrite($fp, "    " . $line);
    
    fwrite($fp, "\n    return " . '$cols;' . "\n  }\n");
    
    $property   = array();
    $property[] = '$property = array();' . "\n\n";
    
    self::writeEngine($property, $schema);
    self::writeUniques($property, $schema);
    self::writeForeignKeys($property, $schema);
    
    fwrite($fp, "\n  public function getProperty()\n  {\n");
    fwrite($fp, "    " . join("", $property));
    fwrite($fp, "    " . 'return $property;' . "\n  }\n}\n");
    fclose($fp);
  }
  
  private static function writeEngine(&$property, $schema)
  {
    $engine = $schema->getTableEngine();
    $property[] = '    $property' . "['tableEngine'] = '{$engine}';\n";
  }

  private static function writeUniques(&$property, $schema)
  {
    $uniques = $schema->getUniques();
    
    if ($uniques === null) {
      $property[] = '    $property' . "['uniques'] = null;\n";
    } else {
      foreach ($uniques as $unique) {
        $us = array();
        foreach ($unique as $u) $us[] = "'" . $u . "'";
        $us = implode(", ", $us);
        $property[] = '    $property' . "['uniques'][] = array({$us});\n";
      }
    }
  }

  private static function writeForeignKeys(&$property, $schema)
  {
    $fkeys = $schema->getForeignKeys();
    
    if ($fkeys === null) {
      $property[] = '    $property' . "['fkeys'] = null;\n";
    } else {
      $space = "                                         ";
      foreach ($fkeys as $column => $params) {
        $property[] = '    $property' . "['fkeys']['{$column}'] = ";
        $property[] = "array('referenced_table'  => '{$params['referenced_table']}',\n";
        $property[] = $space . "'referenced_column' => '{$params['referenced_column']}',\n";
        $property[] = $space . "'on_delete'         => '{$params['on_delete']}',\n";
        $property[] = $space . "'on_update'         => '{$params['on_update']}');\n";
      }
    }
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
    
    Sabel_Sakle_Task::success("generate table list of $cn\n");
    
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

function sakle_schema_help()
{
  echo "Usage: sakle Schema [environment] [-l] [-s ...]\n";
  echo "       -l  create table list\n";
  echo "       -s  create schema: table_name1, table_name2... , or all\n";
  exit;
}
