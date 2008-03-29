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
    
    $environment = $this->getEnvironment();
    define("ENVIRONMENT", $environment);
    $this->initDbConfig();
    
    $connectionName = $this->getConnectionName();
    $directory      = $this->defineMigrationDirectory();
    $this->metadata = Sabel_DB::createMetadata($connectionName);
    
    if ($this->arguments[1] === "export") {
      $this->export();
      self::$execFinalize = false;
    } else {
      $this->currentVersion = $this->getCurrentVersion();
      Sabel_DB_Migration_Manager::setStartVersion($this->currentVersion);
      
      $to = $this->showCurrentVersion($this->arguments);
      $this->files = Sabel_DB_Migration_Manager::getFiles();
      
      if (empty($this->files)) {
        $this->error("no migration files is Found.");
        exit;
      }
      
      if ($this->toVersionNumber($to) !== false) {
        $doNext = $this->execMigration();
        if ($doNext) $this->execNextMigration();
      }
    }
  }
  
  public function finalize()
  {
    if (!self::$execFinalize) return;
    
    $start = Sabel_DB_Migration_Manager::getStartVersion();
    $end   = $this->getCurrentVersion();
    
    $type = ($start < $end) ? "UPGRADE" : "DOWNGRADE";
    $this->success("$type FROM $start TO $end");
  }
  
  protected function getCurrentVersion()
  {
    $connectionName = $this->connectionName;
    $this->stmt = Sabel_DB::createStatement($connectionName);
    Sabel_DB_Migration_Manager::setStatement($this->stmt);
    Sabel_DB_Migration_Manager::setSchema($this->metadata);
    
    try {
      if (!in_array("sversion", $this->metadata->getTableList())) {
        $this->createVersionManageTable();
        return 0;
      } else {
        return $this->getVersion();
      }
    } catch (Exception $e) {
      $this->error($e->getMessage());
      exit;
    }
  }
  
  protected function showCurrentVersion()
  {
    $to = $this->arguments[1];
    
    if ($to === "version" || $to === "-v" || $to === "--version") {
      $this->success("CURRENT VERSION: {$this->currentVersion}");
      exit;
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
  
  protected function execMigration()
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
    
    $dirs = explode(".", Sabel_DB_Config::getPackage($this->connectionName));
    $className = implode("_", array_map("ucfirst", $dirs)) . "_Migration";
    $directory = Sabel_DB_Migration_Manager::getDirectory();
    
    $instance = new $className();
    $instance->execute($directory . DS . $this->files[$num]);
    $this->incrementVersion($next);
    
    return $doNext;
  }
  
  protected function execNextMigration()
  {
    $instance = new self();
    $instance->setArguments($this->arguments);
    $instance->run();
  }
  
  protected function toVersionNumber($to)
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
        $this->arguments[1] = 0;
        $this->execNextMigration();
        $this->success("DOWNGRADE FROM {$this->currentVersion} TO 0");
        $this->arguments[1] = "head";
        $this->execNextMigration();
        $version = $this->getCurrentVersion();
        $this->success("UPGRADE FROM 0 TO $version");
        return self::$execFinalize = false;
      
      case "reset":
        $version = $this->currentVersion;
        $this->arguments[1] = 0;
        $this->execNextMigration();
        $this->success("DOWNGRADE FROM $version TO 0");
        $this->arguments[1] = $version;
        $this->execNextMigration();
        $this->success("UPGRADE FROM 0 TO $version");
        return self::$execFinalize = false;
      
      default:
        $this->error("version '{$to}' is not supported.");
        exit;
    }
  }
  
  protected function incrementVersion($num)
  {
    $sversion = $this->stmt->quoteIdentifier("sversion");
    $version  = $this->stmt->quoteIdentifier("version");
    $this->stmt->setQuery("UPDATE $sversion SET $version = $num")->execute();
  }
  
  protected function initDbConfig()
  {
    Sabel_DB_Config::initialize(new Config_Database());
  }
  
  protected function getConnectionName()
  {
    $args = $this->arguments;
    $name = (isset($args[3])) ? $args[3] : "default";
    if ($name === "-d") $name = "default";
    return $this->connectionName = $name;
  }
  
  protected function createVersionManageTable()
  {
    $stmt = $this->stmt;
    $sversion = $stmt->quoteIdentifier("sversion");
    $id = $stmt->quoteIdentifier("id");
    $version = $stmt->quoteIdentifier("version");
    
    $create = "CREATE TABLE $sversion ("
            . "$id INTEGER NOT NULL PRIMARY KEY, "
            . "$version INTEGER NOT NULL)";
    
    $insert = "INSERT INTO $sversion values(1, 0)";
    
    $stmt->setQuery($create)->execute();
    $stmt->setQuery($insert)->execute();
  }
  
  protected function getVersion()
  {
    $stmt = $this->stmt;
    $sversion = $stmt->quoteIdentifier("sversion");
    $id = $stmt->quoteIdentifier("id");
    $version = $stmt->quoteIdentifier("version");
    
    $rows = $stmt->setQuery("SELECT $version FROM $sversion WHERE $id = 1")->execute();
    return $rows[0]["version"];
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
    echo "  TO_VERSION:  number of target version | head | rehead | reset | foot" . PHP_EOL;
    echo "  CONNECTION_NAME: " . PHP_EOL;
    echo PHP_EOL;
    echo "Example: sakle Migration development head userdb" . PHP_EOL;
    echo PHP_EOL;
  }
}

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
    
    // @todo...
    $writer->write('$create->options("engine", "InnoDB");');
    $writer->close();
    
    $this->fileNum++;
  }
}
