<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());
define("SAKLE_CMD", "sakle");

Sabel::fileUsing("config" . DS . "environment.php");
Sabel::fileUsing("config" . DS . "connection.php");
Sabel::using("Sabel_DB_Migration_Base");
Sabel_DB_Config::initialize();

/**
 * Migration
 *
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Migration extends Sabel_Sakle_Task
{
  private static $execFinalize = true;

  protected $files     = array();
  protected $arguments = array();
  protected $migrateTo = 0;
  protected $accessor  = null;

  protected $connectionName = "";
  protected $currentVersion = 0;

  public function run($arguments)
  {
    if (!isset($arguments[1])) {
      throw new Exception("please specify the environment.");
    }
    
    $this->arguments = $arguments;
    $environment     = $this->getEnvironment();
    $connectionName  = $this->getConnectionName();
    
    $this->initDbConfig($environment);
    $this->accessor  = new Sabel_DB_Schema_Accessor($connectionName);

    if ($arguments[2] === "export") {
      $this->export();
      self::$execFinalize = false;
    } else {
      $this->defineMigrationDir();
      $this->currentVersion = $this->getCurrentVersion();
      Sabel_DB_Migration_Manager::setStartVersion($this->currentVersion);

      $to = $this->showCurrentVersion($arguments);
      $this->files = $this->getMigrationFiles();

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
    $this->printMessage("$type FROM $start TO $end");
  }

  protected function getCurrentVersion()
  {
    $connectionName = $this->connectionName;
    $this->driver = Sabel_DB_Config::loadDriver($connectionName);
    Sabel_DB_Migration_Manager::setDriver($this->driver);
    
    try {
      Sabel_DB_Migration_Manager::setAccessor($this->accessor);
      if (!in_array("sversion", $this->accessor->getTableLists())) {
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

  protected function showCurrentVersion()
  {
    $to = $this->arguments[2];

    if ($to === "version" || $to === "-v" || $to === "--version") {
      $this->printMessage("CURRENT VERSION: {$this->currentVersion}"); exit;
    }

    return $to;
  }

  protected function defineMigrationDir()
  {
    if (!defined("MIG_DIR")) {
      define("MIG_DIR", RUN_BASE . DS . "migration" . DS . $this->connectionName);
    }
  }

  protected function getEnvironment()
  {
    $inputEnv = $this->arguments[1];
    if (($env = environment($inputEnv)) === null) {
      $msg = "environment '{$inputEnv}' is not supported. "
           . "use 'development' or 'test' or 'production'.";

      $this->printMessage($msg, parent::MSG_ERR);
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
      $type   = "upgrade";
      $doNext = ($next < $to);
    } else {
      $next   = $version - 1;
      $num    = $version;
      $type   = "downgrade";
      $doNext = ($next > $to);
    }

    $migration = $this->getMigrationClass($type, $num);
    $migration->execute();
    $this->incrementVersion($next);

    return $doNext;
  }

  protected function execNextMigration()
  {
    $instance = new self();
    $instance->run($this->arguments);
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
        $this->arguments[2] = 0;
        $this->execNextMigration();
        $this->printMessage("DOWNGRADE FROM {$this->currentVersion} TO 0");
        $this->arguments[2] = "head";
        $this->execNextMigration();
        $version = $this->getCurrentVersion();
        $this->printMessage("UPGRADE FROM 0 TO $version");
        return self::$execFinalize = false;

      case "reset":
        $version = $this->currentVersion;
        $this->arguments[2] = 0;
        $this->execNextMigration();
        $this->printMessage("DOWNGRADE FROM $version TO 0");
        $this->arguments[2] = $version;
        $this->execNextMigration();
        $this->printMessage("UPGRADE FROM 0 TO $version");
        return self::$execFinalize = false;

      default:
        $this->printMessage("version '{$to}' is not supported.", parent::MSG_ERR);
        exit;
    }
  }

  protected function incrementVersion($num)
  {
    $this->driver->execute("UPDATE sversion SET version = $num");
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

    return new $className(MIG_DIR . DS . $this->files[$verNum], $type);
  }

  protected function getConnectionName()
  {
    $args = $this->arguments;

    if (!isset($args[2])) {
      $this->printMessage("too few arguments", parent::MSG_ERR); exit;
    }
    
    return $this->connectionName = (isset($args[3])) ? $args[3] : "default";
  }

  protected function createVersionManageTable()
  {
    $create = "CREATE TABLE sversion("
            . "id INTEGER NOT NULL PRIMARY KEY, "
            . "version INTEGER NOT NULL)";

    $insert = "INSERT INTO sversion values(1, 0)";

    $this->driver->execute($create);
    $this->driver->execute($insert);
  }

  protected function getVersion()
  {
    $rows = $this->driver->execute("SELECT version FROM sversion WHERE id = 1");
    return $rows[0]["version"];
  }

  protected function export()
  {
    $exporter = new MigrationExport($this->accessor, $this->connectionName);
    $exporter->export();
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
      $foreignKeys = $schema->getForeignKeys();
      if ($foreignKeys === null) {
        $this->doExport($schema);
        $this->exported[$tblName] = true;
        unset($this->schemas[$tblName]);
        continue;
      }

      $enable = true;
      foreach ($foreignKeys as $key) {
        $parent = $key["referenced_table"];
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

  public function doExport($schema)
  {
    try {
      $tblName = $schema->getTableName();
      if ($tblName === "sversion") return;
      $filePath = $this->path . DS . $this->fileNum . "_"
                . convert_to_modelname($tblName) . "_create.php";

      $fp = fopen($filePath, "w");
      Sabel_DB_Migration_Classes_Restore::forCreate($fp, $schema);

      // @todo table engine.
      fwrite($fp, '$create->options("engine", "InnoDB");');
      fclose($fp);
      $this->fileNum++;
    } catch (Exception $e) {
      $this->printMessage($e->getMessage(), self::MSG_ERR);
      exit;
    }
  }
}
