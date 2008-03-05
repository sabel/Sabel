<?php

/**
 * Schema
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Schema extends Sabel_Sakle_Task
{
  public function run()
  {
    clearstatcache();
    $this->checkInputs();
    
    $environment = environment(strtolower($this->arguments[0]));
    
    if ($environment === null) {
      $this->error("invalid environment.");
      $this->usage();
      exit;
    }
    
    define("ENVIRONMENT", $environment);
    Sabel_DB_Config::initialize(new Config_Database());
    
    $opTables = $this->getOutputTables();
    if (isset($opTables[0]) && $opTables[0] === "all") {
      $writeAll = (count($opTables) === 1);
    } else {
      $writeAll = false;
    }
    
    foreach (Sabel_DB_Config::get() as $connectionName => $params) {
      Sabel_DB_Config::add($connectionName, $params);
      $db = Sabel_DB::createMetadata($connectionName);
      
      foreach ($db->getTableList() as $tblName) {
        if ($writeAll || in_array($tblName, $opTables, true)) {
          $writer = new Sabel_DB_Metadata_FileWriter(SCHEMA_DIR_PATH);
          $writer->write($db->getTable($tblName));
          $this->success("generate Schema 'Schema_" . convert_to_modelname($tblName) . "'");
        }
        
        TableList_Writer::add($connectionName, $tblName);
      }
      
      if (Sabel_Console::hasOption("l", $this->arguments)) {
        TableList_Writer::write($connectionName);
      }
    }
  }
  
  private function getOutputTables()
  {
    if (Sabel_Console::hasOption("t", $this->arguments)) {
      return Sabel_Console::getOption("t", $this->arguments);
    } else {
      return array();
    }
  }
  
  private function checkInputs()
  {
    $args = $this->arguments;
    
    if (count($args) < 2) {
      $this->usage();
      exit;
    } elseif ($args[0] === "--help" || $args[0] === "-h") {
      $this->usage();
      exit;
    }
  }
  
  public function usage()
  {
    echo "Usage: sakle Schema ENVIRONMENT [-l] -t TABLE1 TABLE2..." . PHP_EOL;
    echo PHP_EOL;
    echo "  ENVIRONMENT: production | test | development" . PHP_EOL;
    echo PHP_EOL;
    echo "  -l  output table list\n";
    echo "  -t  output metadata of table\n";
    echo PHP_EOL;
    echo "Example: sakle Schema production -l -t foo bar baz" . PHP_EOL;
    echo PHP_EOL;
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
    
    Sabel_Console::success("generate tablelist of '{$cn}'");
    
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
