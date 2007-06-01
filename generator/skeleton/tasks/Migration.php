<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());

define("SAKLE_CMD", "sakle");

Sabel::fileUsing("tasks/environment.php");
Sabel::fileUsing("config/connection.php");

/**
 * Migration
 *
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Migration extends Sabel_Sakle_Task
{
  protected $files      = array();
  protected $arguments  = array();
  protected $strEnv     = "";
  protected $migrateTo  = 0;
  protected $accessor   = null;

  protected $connectionName = "";
  protected $currentVersion = 0;

  public function run($arguments)
  {
    if (!isset($arguments[1])) {
      throw new Exception("please specify the environment.");
    }

    $environment    = $this->getEnvironment($arguments[1]);
    $connectionName = $this->getConnectionName($arguments);
    $this->connectionName = $connectionName;

    $this->initDbConfig($environment);
    $this->defineMigrationDir();

    $this->currentVersion = $this->getCurrentVersion();
    $to = $this->showCurrentVersion($arguments);
    $this->files = $this->getMigrationFiles();

    if ($this->toVersionNumber($to) !== false) {
      $doNext = $this->execMigration();
      if ($doNext) $this->execNextMigration();
    }
  }

  protected function getCurrentVersion()
  {
    $connectionName = $this->connectionName;
    $this->driver = Sabel_DB_Config::loadDriver($connectionName);
    Sabel_DB_Migration_Manager::setDriver($this->driver);

    try {
      $accessor = new Sabel_DB_Schema_Accessor($connectionName);
      Sabel_DB_Migration_Manager::setAccessor($accessor);

      if (!in_array("sversion", $accessor->getTableLists())) {
        $this->createVersionManageTable();
        return 0;
      } else {
        return $this->getVersion();
      }
    } catch (Exception $e) {
      $this->printMessage($e->getMessage(), self::MSG_ERR); exit;
    }
  }

  protected function getMigrationFiles()
  {
    if (is_dir(MIG_DIR)) {
      return getMigrationFiles(MIG_DIR);
    } else {
      throw new Exception("no such dirctory. '" . MIG_DIR . "'");
    }
  }

  protected function showCurrentVersion($arguments)
  {
    if (!isset($arguments[2])) {
      throw new Exception("too few arguments.");
    }

    $to = $arguments[2];
    if ($to === "version" || $to === "-v" || $to === "--version") {
      $this->printMessage("CURRENT VERSION: {$this->currentVersion}"); exit;
    }

    return $to;
  }

  protected function defineMigrationDir()
  {
    if (!defined("MIG_DIR")) {
      define("MIG_DIR", RUN_BASE . "/migration/" . $this->connectionName);
    }
  }

  protected function getEnvironment($strEnv)
  {
    if (($env = environment($strEnv)) === null) {
      $msg = "please specify either of 'development' or 'test' or 'production'.";
      throw new Exception($msg);
    } else {
      $this->strEnv = $strEnv;
      return $env;
    }
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
      $type   = "upgrade";
      $doNext = ($next < $to);
    } else {
      $next   = $version - 1;
      $num    = $version;
      $type   = "downgrade";
      $doNext = ($next > $to);
    }

    $this->printMessage(strtoupper($type) . " FROM $version TO $next");
    $migration = $this->getMigrationClass($type, $num);
    $migration->execute();
    $this->incrementVersion($next);

    return $doNext;
  }

  protected function execNextMigration()
  {
    $connectionName = $this->connectionName;
    system(SAKLE_CMD . " Migration {$this->strEnv} {$this->migrateTo} $connectionName");
  }

  protected function toVersionNumber($to)
  {
    if (is_numeric($to)) {
      $this->migrateTo = $to; return;
    }

    switch (strtolower($to)) {
      case "head":
        $this->migrateTo = max(array_keys($this->files));
        break;

      case "foot":
        $this->migrateTo = 0;
        break;

      case "rehead":
        $this->migrateTo = 0;
        $this->execNextMigration();
        $this->migrateTo = max(array_keys($this->files));
        $this->execNextMigration();
        return false;

      case "reset":
        $this->migrateTo = 0;
        $this->execNextMigration();
        $this->migrateTo = $this->currentVersion;
        $this->execNextMigration();
        return false;
    }
  }

  protected function incrementVersion($num)
  {
    $query = "UPDATE sversion SET version = $num";
    $this->driver->setSql($query)->execute();
  }

  protected function initDbConfig($environment)
  {
    $params = get_db_params($environment);
    foreach ($params as $connectionName => $param) {
      Sabel_DB_Config::regist($connectionName, $param);
    }
  }

  protected function getMigrationClass($type, $verNum)
  {
    $driverName = Sabel_DB_Config::getDriverName($this->connectionName);
    $driverName = str_replace("pdo-", "", $driverName);
    $className  = "Sabel_DB_Migration_" . ucfirst($driverName);

    return new $className(MIG_DIR . "/" . $this->files[$verNum], $type);
  }

  protected function getConnectionName($arguments)
  {
    return (isset($arguments[3])) ? $arguments[3] : "default";
  }

  protected function createVersionManageTable()
  {
    $create = "CREATE TABLE sversion(id INTEGER PRIMARY KEY, version INTEGER NOT NULL)";
    $insert = "INSERT INTO sversion values(1, 0)";

    $this->driver->setSql(array($create, $insert))->execute();
  }

  protected function getVersion()
  {
    $select = "SELECT version FROM sversion WHERE id = 1";
    $rows   = $this->driver->setSql($select)->execute();

    return $rows[0]["version"];
  }
}

function getMigrationFiles($dirPath)
{
  $handle = opendir($dirPath);

  $files = array();
  while (($file = readdir($handle)) !== false) {
    $num = substr($file, 0, strpos($file, "_"));
    if (is_numeric($num)) $files[$num] = $file;
  }

  return $files;
}

function getFileName($path)
{
  $exp = explode("/", $path);
  return $exp[count($exp) - 1];
}
