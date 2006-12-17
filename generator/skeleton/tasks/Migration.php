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
    $v  = $this->getCurrentVersion();
    $to = $this->arguments[2];
    
    $migrationDir = RUN_BASE . "/migration";
    
    /*
    if (is_dir($migrationDir)) {
      if ($handle = opendir($migrationDir)) {
        while (($file = readdir($handle)) !== false) {
          if ($file{0} !== ".") {
            if (is_file($migrationDir . "/{$file}")) {
              if ($file === "version.php") continue;
              $migrationInstance = $this->makeMigration($migrationDir, $file);
              
              if ($v->version == $file{0}) {
                // here is same versions of current between migration.
                // what do we need?
              } elseif ($v->version < $to && $v->version < $file{0}) {
                $this->upgrade($migrationInstance);
                $v->version = $v->version + 1;
                $v->save();
              } elseif ($v->version > $to && $v->version > $file{0}) {
                $this->downgrace($migrationInstance);
                $v->version = $v->version - 1;
                $v->save();
              }
            }
          }
        }
        closedir($handle);
      }
    }
    */
    
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
          dump($buffer[$nextv]);
          // $migrationInstance = $this->makeMigration($migrationDir, $buffer[$nextv]);
          // $this->upgrade($migrationInstance);
          $v->version += 1;
          $v->save();
        }
      }
      
      /*
      foreach ($buffer as $version => $file) {
        
        if ($v->version < $to && $version < $to) {
          $migrationInstance = $this->makeMigration($migrationDir, $file);
          $this->upgrade($migrationInstance);
          $v->version = $v->version + 1;
          $v->save();
          $this->printMessage("upgrade from {$version} to {$v->version}");
        }
      }
      */
    } elseif ($to < $v->version) {
      // downgrade
      arsort($buffer);
      echo "down grade \n";
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
        $model = MODEL('Sversion');
        $model->setPrimaryKey('id');
        $aVersion = $model->selectOne(1);
      } catch (Exception $e) {
        echo $e->getMessage();
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
  
  protected function upgrade($migration)
  {
    $msg = $migration->upgrade();
    $this->printMessage($msg);
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