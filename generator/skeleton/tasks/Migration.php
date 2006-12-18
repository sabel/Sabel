<?php

define("RUN_BASE", getcwd());

Sabel::using("Sabel_DB_Migration");
Sabel::fileUsing("config/environment.php");
Sabel::fileUsing("config/database.php");
Sabel::fileUsing("Sabel/sabel/db/Functions.php");
Sabel::using('Sabel_DB_Connection');
Sabel::using("Sabel_DB_Model");
Sabel::using("Sabel_DB_Executer");

class Migration extends Sakle
{
  public function execute()
  {
    $this->setupConnection();
    
    if ($this->arguments[2] === "version") {
      $v = $this->getCurrentVersion();
      $this->printMessage($v->version);
      exit;
    }
    
    $v  = $this->getCurrentVersion();
    $this->printMessage("current version: ".$v->version);
    $to = $this->arguments[2];
    
    $migrationDir = RUN_BASE . "/migration";
    
    $buffer = array();
    if (is_dir($migrationDir)) {
      if ($handle = opendir($migrationDir)) {
        while (($file = readdir($handle)) !== false) {
          if ($file{0} !== ".") {
            $buffer[$file{0}] = $file;
          }
        }
      }
    }
    
    if ($v->version < $to) {
      // upgrade
      for ($i = $v->version; $i < $to; $i++) {
        $nextv = $i + 1;
        $this->printMessage("upgrade from {$i} to {$nextv} of $to");
        if (isset($buffer[$nextv])) {
          $migrationInstance = $this->makeMigration($migrationDir, $buffer[$nextv]);
          try {
            $this->upgrade($migrationInstance);
            $v->save(array("version" => ($v->version += 1)));
          } catch (Exception $e) {
            $this->printMessage($e->getMessage(), self::ERR_MSG);
          }
        }
      }
    } elseif ($to < $v->version) {
      // downgrade
      arsort($buffer);
      
      for ($i = $v->version; $i > $to; $i--) {
        $nextv = $i - 1;
        $this->printMessage("downgrade from {$i} to {$nextv} of $to");

        if (isset($buffer[$i])) {
          $migrationInstance = $this->makeMigration($migrationDir, $buffer[$i]);
          try {
            $this->downgrade($migrationInstance);
            $v->save(array("version" => ($v->version -= 1)));
          } catch (Exception $e) {
            $this->printMessage($e->getMessage(), self::ERR_MSG);
          }
        }
      }
    }
  }
  
  protected function getCurrentVersion()
  {
    try {
      $version = MODEL('Sversion');
      $aVersion = $version->selectOne(1);
    } catch (Exception $e) {
      try {
        $this->query("CREATE TABLE sversion(id INT auto_increment PRIMARY KEY, version INT NOT NULL)");
        $version = MODEL('Sversion');
        $version->setPrimaryKey('id');
        $version->version = 0;
        $version->save();
        $aVersion = $version->selectOne(1);
      } catch (Exception $e2) {
        $this->printMessage($e2->getMessage(), self::MSG_ERR);
      }
    }
    return $aVersion;
  }
  
  protected function makeMigration($migrationDir, $file)
  {
    include ($migrationDir . "/$file");
    $fileParts = explode("_", $file);
    $versionNumber = array_shift($fileParts);
    $fileParts = array_map("inner_function_convert_names", $fileParts);
    $className = join("", $fileParts);
    return new $className();
  }
  
  protected function upgrade(Sabel_DB_Migration $migration)
  {
    try {
      $this->printMessage($migration->upgrade());
    } catch (Exception $e) {
      $this->printMessage($e->getMessage(), self::MSG_ERR);
    }
  }
  
  protected function downgrade(Sabel_DB_Migration $migration)
  {
    try {
      $this->printMessage($migration->downgrade());
    } catch (Exception $e) {
      $this->printMessage($e->getMessage(), self::MSG_ERR);
    }
  }
  
  protected function query($sql)
  {
    $e = new Sabel_DB_Executer(array('table'=>'dummy'));
    $e->executeQuery($sql);
  }
  
  protected function setupConnection()
  {
    if (isset($this->arguments[1])) {
      $environment = $this->arguments[1];
    }

    switch ($environment) {
      case "production":
        $env = PRODUCTION;
        break;
      case "test":
        $env = TEST;
        break;
      case "development":
        $env = DEVELOPMENT;
        break;
    }
      foreach (get_db_params($env) as $connectName => $params) {
      Sabel_DB_Connection::addConnection($connectName, $params);
    }
  }
}

function inner_function_convert_names($target)
{
  return ucfirst(str_replace(".php", "", $target));
}