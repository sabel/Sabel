<?php

/**
 * Schema
 *
 * @category  Sakle
 * @package   org.sabel.sakle
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Schema extends Sabel_Sakle_Task
{
  public function run()
  {
    $this->checkInputs();
    
    clearstatcache();
    
    $environment  = environment(strtolower($this->arguments[0]));
    $inputSchemas = $this->getWriteSchemas();
    $schemaWrite  = !empty($inputSchemas);
    
    if ($schemaWrite && $inputSchemas[0] === "all") {
      $schemaAll = (count($inputSchemas) === 1);
    } else {
      $schemaAll = false;
    }
    
    define("ENVIRONMENT", $environment);
    Sabel_DB_Config::initialize(CONFIG_DIR_PATH . DS . "connection" . PHP_SUFFIX);
    
    foreach (get_db_params() as $connectionName => $params) {
      Sabel_DB_Config::add($connectionName, $params);
      $schema = Sabel_DB_Driver::createSchema($connectionName);
      
      foreach ($schema->getTableList() as $tblName) {
        $tblSchema = $schema->getTable($tblName);
        
        if ($schemaAll || $schemaWrite && in_array($tblName, $inputSchemas)) {
          $writer = new Sabel_DB_Schema_FileWriter(SCHEMA_DIR_PATH);
          $writer->write($tblSchema);
          $this->success("generate Schema 'Schema_" . convert_to_modelname($tblName) . "'");
        }
        
        TableList_Writer::add($connectionName, $tblName);
      }
      
      if (Sabel_Command::hasOption("l", $this->arguments)) {
        TableList_Writer::write($connectionName);
      }
    }
  }
  
  private function getWriteSchemas()
  {
    $schemas = array();
    $input = $this->arguments;
    
    if (Sabel_Command::hasOption("s", $this->arguments)) {
      $key = array_search("-s", $input) + 1;
      for ($i = $key, $c = count($input); $i < $c; $i++) {
        if ($val === "-l") break;
        $schemas[] = $input[$i];
      }
    }
    
    return $schemas;
  }
  
  private function checkInputs()
  {
    $args = $this->arguments;
    
    if (count($args) < 2) {
      $this->usage();
      exit;
    } elseif ($args[2] === "--help" || $args[2] === "-h") {
      $this->usage();
      exit;
    }
  }
  
  public function usage()
  {
    echo "Usage: sakle Schema ENVIRONMENT (-l) (-s TABLE1 TABLE2 ...)\n";
    echo "       -l : create table list\n";
    echo "       -s : create schema\n";
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
    
    Sabel_Command::success("generate table list of $cn\n");
    
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
