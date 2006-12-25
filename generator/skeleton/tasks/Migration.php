<?php

define("RUN_BASE", getcwd());

Sabel::fileUsing("config/environment.php");
Sabel::fileUsing("config/database.php");
Sabel::fileUsing("config/connection_map.php");

Sabel::using('Sabel_DB_Migration');
Sabel::using('Sabel_DB_Connection');
Sabel::using('Sabel_DB_Executer');
Sabel::using('Sabel_DB_Model');

/**
 * Migration
 *
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Migration extends Sakle
{
  const TABLE  = 'MIG_TABLE';
  const VIEW   = 'MIG_VIEW';
  const COLUMN = 'MIG_COLUMN';

  protected $environment = '';

  public function execute()
  {
    if (count($this->arguments) < 2) {
      $this->printMessage("Error: invalid parameter.", self::MSG_ERR);
      exit;
    }
    
    $this->initialize();
    $v  = $this->getCurrentVersion();
    $to = $this->arguments[2];
    
    if ($to === "version" || $to === "-v" || $v->version === (int)$to) {
      $this->printMessage("current version: " . $v->version);
      exit;
    }
    
    if (!is_numeric($to)) {
      $this->printMessage("Error: second argument should be a numeric.", self::MSG_ERR);
      exit;
    }
    
    $this->printMessage("current version: " . $v->version);
    $migrationDir = RUN_BASE . "/migration";
    
    $files = array();
    if (is_dir($migrationDir) && ($handle = opendir($migrationDir))) {
      while (($file = readdir($handle)) !== false) {
        $fileNames = split("_", $file);
        $versionNumberOfFile = array_shift($fileNames);
        if ($file{0} !== "." && is_numeric($versionNumberOfFile)) {
          $files[$versionNumberOfFile] = $file;
        }
      }
    }
    
    $doNext = false;
    if ($v->version < $to) {
      $next   = $v->version + 1;
      $ver    = $next;
      $method = 'upgrade';
      $doNext = ($next < $to);
    } elseif ($to < $v->version) {
      $next   = $v->version - 1;
      $ver    = $v->version;
      $method = 'downgrade';
      $doNext = ($next > $to);
    }
    
    $this->printMessage("$method from {$v->version} to {$next}");
    
    if (isset($files[$ver])) {
      $file = $files[$ver];
    } else {
      $this->printMessage("migration file is not found. file version: {$ver}", self::MSG_ERR);
      exit;
    }
    
    $migrationInstance = $this->makeMigration($migrationDir, $file);
    $this->$method($migrationInstance);
    
    try {
      $v->execute("UPDATE sversion SET version = $next WHERE id = 1");
    } catch (Exception $e) {
      $this->printMessage($e->getMessage(), self::MSG_ERR);
      exit;
    }
    
    $v->commit();
    
    if ($doNext) {
      system("sakle Migration {$this->arguments[1]} {$to}");
    }
  }
  
  protected function initialize()
  {
    $env = '';
    switch ($this->arguments[1]) {
      case 'production':
        $env = $this->environment = PRODUCTION;
        break;
      case 'test':
        $env = $this->environment = TEST;
        break;
      case 'development':
        $env = $this->environment = DEVELOPMENT;
        break;
    }
    
    if ($env === '') {
      $msg = "Error: wrong environment. 'production' or 'test' or 'development'.";
      $this->printMessage($msg, self::MSG_ERR);
      exit;
    }
    
    foreach (get_db_params($env) as $connectName => $params) {
      Sabel_DB_Connection::addConnection($connectName, $params);
    }
    Sabel_DB_Connection::setInit(true);
  }
  
  protected function migrateVersion()
  {
    
  }
  
  protected function getCurrentVersion()
  {
    $model  = MODEL('Sversion');
    $tables = $model->getTableNames();
    
    try {
      if (in_array('sversion', $tables)) {
        $aVersion = $model->selectOne(1);
      } else {
        $driver = $model->getDriver();
        $driver->execute("CREATE TABLE sversion(id INTEGER PRIMARY KEY, version INTEGER NOT NULL)");
        $driver->execute("INSERT INTO sversion values(1, 0)");
        
        Sabel_DB_SimpleCache::clear();
        $aVersion = MODEL('Sversion')->selectOne(1);
      }
    } catch (Exception $e) {
      $this->printMessage($e->getMessage(), self::MSG_ERR);
      exit;
    }
    
    $aVersion->begin();
    return $aVersion;
  }
  
  protected function makeMigration($migrationDir, $file)
  {
    include ($migrationDir . "/$file");
    $fileParts  = explode("_", $file);
    $versionNum = array_shift($fileParts);
    $fileParts  = array_map("inner_function_convert_names", $fileParts);
    $className  = join("", $fileParts) . $versionNum;
    
    return new $className($this->environment);
  }
  
  protected function upgrade(Sabel_DB_Migration $migration)
  {
    try {
      $this->printMessage($migration->upgrade());
    } catch (Exception $e) {
      $this->printMessage($e->getMessage(), self::MSG_ERR);
      exit;
    }
  }
  
  protected function downgrade(Sabel_DB_Migration $migration)
  {
    try {
      $this->printMessage($migration->downgrade());
    } catch (Exception $e) {
      $this->printMessage($e->getMessage(), self::MSG_ERR);
      exit;
    }
  }
  
  protected function query($sql)
  {
    $e = new Sabel_DB_Executer(array('table' => 'dummy'));
    $e->executeQuery($sql);
  }
}

function inner_function_convert_names($target)
{
  return ucfirst(str_replace(".php", "", $target));
}
