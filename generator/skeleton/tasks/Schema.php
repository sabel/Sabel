<?php

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
    
    $environment  = environment(strtolower($arguments[1]));
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
          $writer = new Sabel_DB_Schema_FileWriter(SCHEMA_DIR_PATH);
          $writer->write($schema);
          $this->printMessage("generate Schema 'Schema_" . convert_to_modelname($tblName) . "'");
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
  
  private function checkInputs($arguments)
  {
    if (count($arguments) < 3) {
      sakle_schema_help(); exit;
    } elseif ($arguments[2] === "--help" || $arguments[2] === "-h") {
      sakle_schema_help(); exit;
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
    $target    = SCHEMA_DIR_PATH . DS . "{$fileName}.php";
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
