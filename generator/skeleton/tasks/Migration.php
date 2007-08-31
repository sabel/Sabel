<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());

Sabel::fileUsing("config" . DS . "environment.php");
Sabel::fileUsing("config" . DS . "connection.php");

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

    Sabel_DB_Config::initialize();

    $this->arguments = $arguments;
    $environment     = $this->getEnvironment();
    $connectionName  = $this->getConnectionName();
    $directory       = $this->getMigrationDirectory();
    $this->accessor  = new Sabel_DB_Schema_Accessor($connectionName);
    $this->initDbConfig($environment);

    if ($arguments[2] === "export") {
      $this->export();
      self::$execFinalize = false;
    } else {
      $this->currentVersion = $this->getCurrentVersion();
      Sabel_DB_Migration_Manager::setStartVersion($this->currentVersion);

      $to = $this->showCurrentVersion($arguments);
      $this->files = Sabel_DB_Migration_Manager::getFiles();

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
      if (!in_array("sversion", $this->accessor->getTableList())) {
        $this->createVersionManageTable();
        return 0;
      } else {
        return $this->getVersion();
      }
    } catch (Exception $e) {
      $this->printMessage($e->getMessage(), self::MSG_ERR); exit;
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

  protected function getMigrationDirectory()
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

    if (strpos($driverName, "pdo") !== false) {
      $driverName = str_replace("pdo-", "", $driverName);
    } elseif ($driverName === "mysqli") {
      $driverName = "mysql";
    }

    $className = "Sabel_DB_" . ucfirst($driverName) . "_Migration";
    $directory = Sabel_DB_Migration_Manager::getDirectory();
    return new $className($directory . DS . $this->files[$verNum], $type);
  }

  protected function getConnectionName()
  {
    $args = $this->arguments;

    if (!isset($args[2])) {
      $this->printMessage("too few arguments", parent::MSG_ERR);
      exit;
    } else {
      $name = (isset($args[3])) ? $args[3] : "default";
      return $this->connectionName = $name;
    }
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
    $tblName = $schema->getTableName();
    if ($tblName === "sversion") return;
    $filePath = $this->path . DS . $this->fileNum . "_"
              . convert_to_modelname($tblName) . "_create.php";

    $writer = new Sabel_DB_Migration_Writer($filePath);
    $writer->writeTable($schema);
    $fp =& $writer->getFilePointer();

    // @todo table engine.
    fwrite($fp, '$create->options("engine", "InnoDB");');
    $writer->close();
    $this->fileNum++;
  }
}
