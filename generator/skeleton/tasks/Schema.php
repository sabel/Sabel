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
    
    $outputDir   = RUN_BASE . DS . LIB_DIR_NAME . DS . "schema";
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
    
    $tList = new TableListWriter($outputDir);
    foreach (Sabel_DB_Config::get() as $connectionName => $params) {
      Sabel_DB_Config::add($connectionName, $params);
      $db = Sabel_DB::createMetadata($connectionName);
      
      foreach ($db->getTableList() as $tblName) {
        if ($writeAll || in_array($tblName, $opTables, true)) {
          $writer = new Sabel_DB_Metadata_FileWriter($outputDir);
          $writer->write($db->getTable($tblName));
          $this->success("generate Schema 'Schema_" . convert_to_modelname($tblName) . "'");
        }
        
        $tList->add($connectionName, $tblName);
      }
      
      if (Sabel_Console::hasOption("l", $this->arguments)) {
        $tList->write($connectionName);
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

class TableListWriter
{
  private $tables = array();
  private $outputDir = "";
  
  public function __construct($outputDir)
  {
    if (is_dir($outputDir)) {
      $this->outputDir = $outputDir;
    } else {
      $message = "no such file or directory.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function add($connectionName, $tblName)
  {
    $this->tables[$connectionName][] = $tblName;
  }
  
  public function get($connectionName)
  {
    return $this->tables[$connectionName];
  }
  
  public function write($connectionName)
  {
    $cn        = $connectionName;
    $fileName  = ucfirst($cn) . "TableList";
    $className = "Schema_" . $fileName;
    
    Sabel_Console::success("generate tablelist of '{$cn}'");
    
    $contents = array();
    $contents[] = "<?php" . PHP_EOL;
    $contents[] = "class $className";
    $contents[] = "{";
    $contents[] = "  public function get()";
    $contents[] = "  {";
    
    $tables = array_map(create_function('$v', 'return \'"\' . $v . \'"\';'), $this->tables[$cn]);
    
    $contents[] = "    return array(" . implode(", ", $tables) . ");";
    $contents[] = "  }";
    $contents[] = "}";
    
    $fp = fopen($this->outputDir . DS . $fileName . ".php", "w");
    fwrite($fp, implode(PHP_EOL, $contents));
    fclose($fp);
  }
}
