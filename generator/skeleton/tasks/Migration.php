<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());

Sabel::fileUsing("tasks/environment.php");
Sabel::fileUsing("config/database.php");

Sabel::using('Sabel_Sakle_Task');
Sabel::using('Sabel_DB_Migration');
Sabel::using('Sabel_DB_Connection');
Sabel::using('Sabel_DB_Model');

/**
 * task of migration
 *
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Migration extends Sabel_Sakle_Task
{
  const TABLE  = 'MIG_TABLE';
  const VIEW   = 'MIG_VIEW';
  const COLUMN = 'MIG_COLUMN';

  protected $environment = null;
  protected $constEnvironment = null;
  
  protected $migrationDir = '';
  protected $migrationFiles = null;
  
  protected $migrateTo = 0;
  protected $version   = null;

  public function run($arguments)
  {
    list($environment, $migrateTo, $connectName) = $this->processArguments($arguments);
    
    if (!defined("ENVIRONMENT")) {
      if (isset($environment)) {
        define ("ENVIRONMENT", environment($environment));
      } else {
        define ("ENVIRONMENT", TEST);
      }
    }
    
    $this->initializeEnvironment($environment);
    $this->initializeDatabaseConnection();
    
    $this->version   = $this->getCurrentVersion($connectName);
    $this->migrateTo = $migrateTo;
    
    $this->showCurrentDatabaseVersion($this->migrateTo);
    
    $this->migrationDir   = RUN_BASE . "/migration/" . $connectName;
    $this->migrationFiles = $this->getMigrationFiles($this->migrationDir);
    
    $this->decideMigrate($connectName);
    
    $this->checkMigrateTo($this->migrateTo);
    
    $hasNextMigration = $this->doCurrentMigration($connectName);
    
    if ($hasNextMigration) {
      $this->doNewMigration($this->environment, $connectName);
    }
  }
  
  private function processArguments($arguments)
  {
    return array($arguments[1],
                 $arguments[2],
                 (isset($arguments[3])) ? $arguments[3] : 'default');
  }
  
  private function showCurrentDatabaseVersion($migrateTo)
  {
    if ($migrateTo === "version" || $migrateTo === "-v") {
      $this->printMessage("current version: " . $this->version->version);
      exit;
    }
  }
  
  protected function checkMigrateTo($migrateTo)
  {
    if (!is_numeric($migrateTo)) {
      $this->printMessage("Error: second argument should be a numeric.", self::MSG_ERR);
      exit;
    }
  }
  
  protected function doCurrentMigration($connectName)
  {
    $this->printMessage("current version: " . $this->version->version);
    
    $currentVersion = $this->version->version;
    $doNext = false;
    if ($currentVersion < $this->migrateTo) {
      $next    = $currentVersion + 1;
      $version = $next;
      $method  = 'upgrade';
      $doNext  = ($next < $this->migrateTo);
    } elseif ($this->migrateTo < $currentVersion) {
      $next    = $currentVersion - 1;
      $version = $currentVersion;
      $method  = 'downgrade';
      $doNext  = ($next > $this->migrateTo);
    } else {
      return false;
    }
    
    $this->printMessage("$method from {$currentVersion} to {$next}");
    
    if (isset($this->migrationFiles[$version])) {
      $migration = $this->migrationFiles[$version];
    } else {
      $this->printMessage("migration file is not found. file version: {$version}", self::MSG_ERR);
      exit;
    }
    
    $migrationInstance = $this->makeMigration($migration, $connectName);
    
    try {
      $migrationInstance->$method();
      $this->version->executeQuery("UPDATE sversion SET version = $next WHERE id = 1");
    } catch (Exception $e) {
      $this->printMessage($e->getMessage(), self::MSG_ERR);
      exit;
    }
    
    return $doNext;
  }
  
  private function doNewMigration($environment, $connectName)
  {
    $nins = new self();
    $nins->run(array(null, $environment, $this->migrateTo, $connectName));
    unset($nins);
  }
  
  protected function getMigrationFiles($migrationDir)
  {
    $files = array();
    if (is_dir($migrationDir) && ($handle = opendir($migrationDir))) {
      while (($file = readdir($handle)) !== false) {
        $versionNumberOfFile = substr($file, 0, strpos($file, '_'));
        if (is_numeric($versionNumberOfFile)) $files[$versionNumberOfFile] = $file;
      }
    }
    return $files;
  }
  
  protected function decideMigrate($connectName)
  {
    switch (strtolower($this->migrateTo)) {
      case 'head':
        $this->migrateTo = max(array_keys($this->migrationFiles));
        break;
      case 'foot':
        $this->migrateTo = 0;
        break;
      case 'rehead':
        $this->migrateTo = 0;
        $this->doNewMigration($this->environment, $connectName);
        $this->migrateTo = max(array_keys($this->migrationFiles));
        $this->doNewMigration($this->environment, $connectName);
        $this->version = $this->getCurrentVersion($connectName);
        break;
      case 'reset':
        $this->migrateTo = 0;
        $this->doNewMigration($this->environment, $connectName);
        $this->migrateTo = $this->version->version;
        $this->doNewMigration($this->environment, $connectName);
        $this->version = $this->getCurrentVersion($connectName);
        break;
    }
  }
  
  private function initializeEnvironment($strEnvironment)
  {
    $constEnvironment = $this->getConstantEnvironment($strEnvironment);
    
    if ($constEnvironment === null) {
      $fp = fopen("php://stdin", "r");
      
      while (true) {
        fputs($fp, "specify valid environment ( production | test | development ): ");
        $tmporaryEnvironment = trim(fgets($fp));
        $tmporaryConstEnvironment = $this->getConstantEnvironment($tmporaryEnvironment);
        if ($tmporaryConstEnvironment !== null) {
          $strEnvironment = $tmporaryConstEnvironment;
          $constEnvironment = $tmporaryConstEnvironment;
          break;
        }
      }
      
      fclose($fp);
    }
    
    $this->environment      = $strEnvironment;
    $this->constEnvironment = $constEnvironment;
  }
  
  private function getConstantEnvironment($strEnvironment)
  {
    $constEnvironment = null;
    
    switch (strtolower($strEnvironment)) {
      case 'production':
        $constEnvironment = PRODUCTION;
        break;
      case 'test':
        $constEnvironment = TEST;
        break;
      case 'development':
        $constEnvironment = DEVELOPMENT;
        break;
    }
    
    return $constEnvironment;
  }
  
  private function initializeDatabaseConnection()
  {
    foreach (get_db_params($this->constEnvironment) as $connectName => $params) {
      Sabel_DB_Connection::addConnection($connectName, $params);
    }
    
    Sabel_DB_Connection::setInitFlag(true);
  }
  
  private function getCurrentVersion($connectName)
  {
    $driver  = Sabel_DB_Connection::getDriver($connectName);
    $scmName = Sabel_DB_Connection::getSchema($connectName);
    
    try {
      $accessor = Sabel::load('Sabel_DB_Schema_Accessor', $connectName, $scmName);
      if (!in_array('sversion', $accessor->getTableNames())) {
        $driver->execute("CREATE TABLE sversion(id INTEGER PRIMARY KEY, version INTEGER NOT NULL)");
        $driver->execute("INSERT INTO sversion values(1, 0)");
      }
      $model = MODEL('Sversion');
      $model->setConnectName($connectName);
      $aVersion = $model->selectOne(1);
    } catch (Exception $e) {
      $this->printMessage($e->getMessage(), self::MSG_ERR);
      exit;
    }
    
    return $aVersion;
  }
  
  private function makeMigration($file, $connectName)
  {
    $fileParts  = explode("_", $file);
    $versionNum = array_shift($fileParts);
    $fileParts  = array_map("inner_function_convert_names", $fileParts);
    $className  = join("", $fileParts) . $versionNum;
    if (!class_exists($className)) require_once ($this->migrationDir . "/" . $file);
    return new $className($this->constEnvironment, $connectName);
  }
}

function inner_function_convert_names($target)
{
  return ucfirst(str_replace(".php", "", $target));
}
