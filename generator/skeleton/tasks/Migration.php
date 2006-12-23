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
  public function execute()
  {
    $v  = $this->getCurrentVersion();
    $to = $this->arguments[2];
    
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
    
    if ($v->version < $to) {
      // upgrade
      for ($i = $v->version; $i < $to; $i++) {
        $nextv = $i + 1;
        $this->printMessage("upgrade from {$i} to {$nextv} of $to");
        if (isset($files[$nextv])) {
          $migrationInstance = $this->makeMigration($migrationDir, $files[$nextv]);
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
      arsort($files);
      
      for ($i = $v->version; $i > $to; $i--) {
        $nextv = $i - 1;
        $this->printMessage("downgrade from {$i} to {$nextv} of $to");
        if (isset($files[$i])) {
          $migrationInstance = $this->makeMigration($migrationDir, $files[$i]);
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
  
  protected function migrateVersion()
  {
    
  }
  
  protected function getCurrentVersion()
  {
    try {
      $version  = MODEL('Sversion');
      $aVersion = $version->selectOne(1);
    } catch (Exception $e) {
      try {
        $this->query("CREATE TABLE sversion(id INTEGER PRIMARY KEY, version INTEGER NOT NULL)");
        $version = MODEL('Sversion');
        $version->id = 1;
        $version->version = 0;
        $version->save();

        $aVersion = $version->selectOne('id', 1);
      } catch (Exception $e2) {
        $this->printMessage($e2->getMessage(), self::MSG_ERR);
      }
    }

    $this->printMessage("current version: " . $aVersion->version);
    return $aVersion;
  }
  
  protected function makeMigration($migrationDir, $file)
  {
    include ($migrationDir . "/$file");
    $fileParts  = explode("_", $file);
    $versionNum = array_shift($fileParts);
    $fileParts  = array_map("inner_function_convert_names", $fileParts);
    $className  = join("", $fileParts) . $versionNum;
    
    if (isset($this->arguments[1])) {
      return new $className($this->arguments[1]);
    } else {
      throw new Exception('Error: please specify the environment.');
    }
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
    $e = new Sabel_DB_Executer(array('table' => 'dummy'));
    $e->executeQuery($sql);
  }
}

function inner_function_convert_names($target)
{
  return ucfirst(str_replace(".php", "", $target));
}
