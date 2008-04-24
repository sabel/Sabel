<?php

/**
 * Migration
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Migration extends Sabel_Sakle_Task
{
  protected static $versions = array();
  private static $execFinalize = true;
  
  protected $stmt      = null;
  protected $files     = array();
  protected $migrateTo = 0;
  protected $metadata  = null;
  
  protected $connectionName = "";
  protected $currentVersion = 0;
  
  public function run()
  {
    if (count($this->arguments) < 2) {
      $this->error("to few arguments.");
      $this->usage();
      exit;
    }
    
    if (!defined("ENVIRONMENT")) {
      define("ENVIRONMENT", $this->getEnvironment());
    }
    
    Sabel_DB_Config::initialize(new Config_Database());
    
    $connectionName = $this->getConnectionName();
    $this->stmt     = Sabel_DB::createStatement($connectionName);
    $this->metadata = Sabel_DB::createMetadata($connectionName);
    $directory = $this->defineMigrationDirectory();
    
    /* @todo
    if ($this->arguments[1] === "export") {
      $this->export();
      self::$execFinalize = false;
    } else {
    */
      $tables  = array();
      $tblName = $this->arguments[1];
      if (strtolower($tblName) === "all") {
        $enableCommands = array("head", "foot", "rehead");
        if (!in_array($this->arguments[2], $enableCommands, true)) {
          $this->error("invalid command for 'all'.");
          exit;
        }
        
        foreach (scandir($directory) as $item) {
          if ($item{0} === ".") continue;
          $tables[] = $item;
        }
      } else {
        $tables[] = $tblName;
      }
      
      foreach ($tables as $table) {
        $this->execMigration($table);
      }
    //}
  }
  
  protected function execMigration($tblName)
  {
    $this->arguments[1]   = $tblName;
    $this->currentVersion = $this->getCurrentVersion($tblName);
    
    if (!isset(self::$versions[$tblName]["start"])) {
      self::$versions[$tblName]["start"] = $this->currentVersion;
    }
    
    $to = $this->showCurrentVersion();
    $this->files = Sabel_DB_Migration_Manager::getFiles($tblName);
    
    if (empty($this->files)) {
      $this->error("No migration files is found.");
      exit;
    }
    
    if ($this->toVersionNumber($to, $tblName) !== false) {
      $doNext = $this->_execMigration($tblName);
      if ($doNext) $this->execNextMigration();
    }
  }
  
  public function finalize()
  {
    if (!self::$execFinalize) return;
    
    foreach (self::$versions as $tblName => $version) {
      $start = $version["start"];
      if (isset($version["end"])) {
        $end  = $version["end"];
        $mode = ($start < $end) ? "UPGRADE" : "DOWNGRADE";
        $this->success("({$tblName}) $mode FROM $start TO $end");
      } else {
        $this->message("({$tblName}) NO CHANGES FROM $start");
      }
    }
  }
  
  protected function getCurrentVersion($tblName)
  {
    Sabel_DB_Migration_Manager::setStatement($this->stmt);
    Sabel_DB_Migration_Manager::setSchema($this->metadata);
    
    try {
      if (!in_array("sbl_version", $this->metadata->getTableList())) {
        $this->createVersioningTable();
        return 0;
      } else {
        return $this->getVersion($tblName);
      }
    } catch (Exception $e) {
      $this->error($e->getMessage());
      exit;
    }
  }
  
  protected function showCurrentVersion()
  {
    $opts = array("-v", "--version");
    
    if (isset($this->arguments[2])) {
      $to = $this->arguments[2];
      if (in_array($to, $opts, true)) {
        $this->success("CURRENT VERSION: {$this->currentVersion}");
        exit;
      }
    } else {
      $to = $this->arguments[1];
      if (in_array($to, $opts, true)) {
        $tblName = $this->stmt->quoteIdentifier("sbl_version");
        $rows = $this->stmt->setQuery("SELECT * FROM $tblName")->execute();
        foreach ($rows as $row) {
          $this->success("({$row["tblname"]}) CURRENT VERSION: {$row["version"]}");
        }
        exit;
      }
    }
    
    return $to;
  }
  
  protected function defineMigrationDirectory()
  {
    if (in_array("-d", $this->arguments, true)) {
      $index = array_search("-d", $this->arguments) + 1;
      $dir = $this->arguments[$index];
    } else {
      $dir = RUN_BASE . DS . "migration" . DS . $this->connectionName;
    }
    
    Sabel_DB_Migration_Manager::setDirectory($dir);
    
    return $dir;
  }
  
  protected function getEnvironment()
  {
    $inputEnv = $this->arguments[0];
    if (($env = environment($inputEnv)) === null) {
      $this->error("invalid environment. use 'development' or 'test', 'production'.");
      exit;
    }
    
    return $env;
  }
  
  protected function _execMigration($tblName)
  {
    $version = $this->currentVersion;
    
    $to = (int)$this->migrateTo;
    if ((int)$version === $to) return false;
    
    $doNext = false;
    if ($version < $to) {
      $next   = $version + 1;
      $num    = $next;
      $mode   = "upgrade";
      $doNext = ($next < $to);
    } else {
      $next   = $version - 1;
      $num    = $version;
      $mode   = "downgrade";
      $doNext = ($next > $to);
    }
    
    Sabel_DB_Migration_Manager::setApplyMode($mode);
    
    $instance  = Sabel_DB::createMigration($this->connectionName);
    $directory = Sabel_DB_Migration_Manager::getDirectory($tblName);
    $instance->execute($tblName, $directory . DS . $this->files[$num]);
    $this->updateVersionNumber($tblName, $next);
    
    return $doNext;
  }
  
  protected function execNextMigration()
  {
    $instance = new self();
    $instance->setArguments($this->arguments);
    $instance->run();
  }
  
  protected function toVersionNumber($to, $tblName)
  {
    if (is_numeric($to)) {
      return $this->migrateTo = $to;
    }
    
    switch (strtolower($to)) {
      case "head":
        $this->migrateTo = max(array_keys($this->files));
        break;
      
      case "foot":
        $this->migrateTo = 0;
        break;
      
      case "rehead":
        $this->arguments[2] = 0;
        $this->execNextMigration();
        $this->success("({$tblName}) DOWNGRADE FROM {$this->currentVersion} TO 0");
        $this->arguments[2] = "head";
        $this->execNextMigration();
        $version = $this->getCurrentVersion($tblName);
        $this->success("({$tblName}) UPGRADE FROM 0 TO $version");
        $this->arguments[2] = "rehead";
        return self::$execFinalize = false;
      
      default:
        $this->error("version '{$to}' is not supported.");
        exit;
    }
  }
  
  protected function updateVersionNumber($tblName, $num)
  {
    $stmt    = $this->stmt;
    $table   = $stmt->quoteIdentifier("sbl_version");
    $tn      = $stmt->quoteIdentifier("tblname");
    $version = $stmt->quoteIdentifier("version");
    
    $query = "SELECT COUNT(*) AS cnt FROM $table WHERE $tn = @tblname@";
    $rows = $stmt->setQuery($query)->setBindValue("tblname", $tblName)->execute();
    
    if ((int)$rows[0]["cnt"] === 0) {
      $query = "INSERT INTO $table VALUES(@tblname@, 1)";
    } else {
      $query = "UPDATE $table SET $version = $num WHERE $tn = @tblname@";
    }
    
    $stmt->setQuery($query)->setBindValue("tblname", $tblName)->execute();
    self::$versions[$tblName]["end"] = $num;
  }
  
  protected function getConnectionName()
  {
    $args = $this->arguments;
    $name = (isset($args[3])) ? $args[3] : "default";
    if ($name === "-d") $name = "default";
    return $this->connectionName = $name;
  }
  
  protected function createVersioningTable()
  {
    $stmt     = $this->stmt;
    $sversion = $stmt->quoteIdentifier("sbl_version");
    $tblname  = $stmt->quoteIdentifier("tblname");
    $version  = $stmt->quoteIdentifier("version");
    
    $create = <<<SQL
CREATE TABLE $sversion
(
  $tblname VARCHAR(64) NOT NULL PRIMARY KEY,
  $version INTEGER NOT NULL
)
SQL;
    
    $stmt->setQuery($create)->execute();
  }
  
  protected function getVersion($tblName)
  {
    $stmt    = $this->stmt;
    $table   = $stmt->quoteIdentifier("sbl_version");
    $version = $stmt->quoteIdentifier("version");
    $query   = "SELECT $version FROM $table WHERE tblname = @tblname@";
    
    $rows = $stmt->setQuery($query)
                 ->setBindValue("tblname", $tblName)
                 ->execute();
    
    return (isset($rows[0]["version"])) ? $rows[0]["version"] : 0;
  }
  
  protected function export()
  {
    $exporter = new MigrationExport($this->metadata, $this->connectionName);
    $exporter->export();
  }
  
  public function usage()
  {
    echo "Usage: sakle Migration ENVIRONMENT TO_VERSION [CONNECTION_NAME] " . PHP_EOL;
    echo PHP_EOL;
    echo "  ENVIRONMENT: production | test | development" . PHP_EOL;
    echo "  TO_VERSION:  number of target version | head | foot | rehead" . PHP_EOL;
    echo "  CONNECTION_NAME: " . PHP_EOL;
    echo PHP_EOL;
    echo "Example: sakle Migration development head userdb" . PHP_EOL;
    echo PHP_EOL;
  }
}

/*
class MigrationExport
{
  private $fileNum  = 1;
  private $path     = "";
  private $schemas  = array();
  private $exported = array();
  
  public function __construct($accessor, $connectionName)
  {
    $this->schemas = $accessor->getAll();
    $this->path = RUN_BASE . DS . "migration" . DS . $connectionName;
  }
  
  public function export()
  {
    if (empty($this->schemas)) return;
    
    foreach ($this->schemas as $tblName => $schema) {
      $fkey = $schema->getForeignKey();
      if ($fkey === null) {
        $this->doExport($schema);
        $this->exported[$tblName] = true;
        unset($this->schemas[$tblName]);
        continue;
      }
      
      $enable = true;
      foreach ($fkey->toArray() as $key) {
        $parent = $key->table;
        if ($parent === $tblName) continue;
        if (!isset($this->exported[$parent])) {
          $enable = false;
          break;
        }
      }
      
      if ($enable) {
        $this->doExport($schema);
        $this->exported[$tblName] = true;
        unset($this->schemas[$tblName]);
      }
    }
    
    $this->export();
  }
  
  public function doExport($tblSchema)
  {
    $tblName = $tblSchema->getTableName();
    if ($tblName === "sversion") return;
    
    $fileName = $this->fileNum . "_" . convert_to_modelname($tblName) . "_create.php";
    $filePath = $this->path . DS . $fileName;
    
    Sabel_Console::success("$fileName");
    
    $writer = new Sabel_DB_Migration_Writer($filePath);
    $writer->writeTable($tblSchema);
    
    // @todo
    $writer->write('$create->options("engine", "InnoDB");');
    $writer->close();
    
    $this->fileNum++;
  }
}
*/
