<?php

Sabel::fileUsing(dirname(__FILE__) . DIR_DIVIDER . "Functions.php", true);

/**
 * Sabel_DB_Migration_Base
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Migration_Base
{
  protected $applyMode = "";
  protected $filePath  = "";
  protected $dirPath   = "";
  protected $mdlName   = "";
  protected $command   = "";
  protected $version   = 0;
  protected $pkeys     = array();
  protected $fkeys     = array();
  protected $uniques   = array();

  abstract protected function getBooleanAttr($value);

  public function __construct($filePath, $applyMode, $dirPath = null)
  {
    $this->applyMode = $applyMode;
    $this->filePath  = $filePath;
    $this->dirPath   = ($dirPath === null) ? MIG_DIR : $dirPath;

    $file = getFileName($filePath);
    @list ($num, $mdlName, $command) = explode("_", $file);

    $this->version = $num;
    $this->mdlName = $mdlName;

    if ($mdlName === "mix.php") {
      $this->command = "custom";
    } elseif ($mdlName === "query.php") {
      $this->command = "query";
    } elseif (($pos = strpos($command, ".")) !== false) {
      $this->command = substr($command, 0, $pos);
    } else {
      $this->command = $command;
    }

    Sabel_DB_Migration_Manager::setApplyMode($applyMode);
  }

  public function setOptions($key, $val) {}

  public function setPrimaryKeys($pkeys)
  {
    $this->pkeys = $pkeys;
  }

  public function setForeignKeys($fkeys)
  {
    $this->fkeys = $fkeys;
  }

  public function setUniques($uniques)
  {
    $this->uniques = $uniques;
  }

  public function execute()
  {
    clearstatcache();

    $command = $this->command;
    if (method_exists($this, $command)) {
      $this->$command();
    } else {
      throw new Sabel_DB_Exception("command '$command' not found.");
    }
  }

  public function create()
  {
    $tblName = convert_to_tablename($this->mdlName);

    if ($this->applyMode === "upgrade") {
      $this->createUpgrade($tblName);
    } else {
      if (is_table_exists($tblName)) {
        executeQuery("DROP TABLE " . convert_to_tablename($this->mdlName));
      } else {
        Sabel_Sakle_Task::warning("unknown table '{$tblName}'. (SKIP)");
      }
    }
  }

  protected function createUpgrade($tblName)
  {
    if (is_table_exists($tblName)) {
      Sabel_Sakle_Task::warning("table '{$tblName}' already exists. (SKIP)");
    } else {
      $this->createTable(getCreate($this->filePath, $this));
    }
  }

  public function drop()
  {
    if ($this->applyMode === "upgrade") {
      $restore = $this->getRestoreFileName();
      if (is_file($restore)) unlink($restore);

      writeTable(getSchema($this->mdlName), $restore);
      executeQuery("DROP TABLE " . convert_to_tablename($this->mdlName));
    } else {
      $path = $this->getRestoreFileName();
      $this->createTable(getCreate($path, $this));
    }
  }

  public function addColumn()
  {
    $columns = getAddColumns($this->filePath);

    if ($this->applyMode === "upgrade") {
      $this->execAddColumn($columns);
    } else {
      $tblName = convert_to_tablename($this->mdlName);
      foreach ($columns as $column) {
        executeQuery("ALTER TABLE $tblName DROP COLUMN " . $column->name);
      }
    }
  }

  protected function execAddColumn($columns)
  {
    $tblName = convert_to_tablename($this->mdlName);
    $names   = getSchema($this->mdlName)->getColumnNames();

    foreach ($columns as $column) {
      if (in_array($column->name, $names)) {
        Sabel_Sakle_Task::warning("duplicate column name '{$column->name}'. (SKIP)");
      } else {
        $line = $this->createColumnAttributes($column);
        executeQuery("ALTER TABLE $tblName ADD " . $line);
      }
    }
  }

  public function dropColumn()
  {
    if ($this->applyMode === "upgrade") {
      $restore = $this->getRestoreFileName();
      if (is_file($restore)) unlink($restore);

      $columns  = getDropColumns($this->filePath);
      $tblName  = convert_to_tablename($this->mdlName);
      $schema   = getSchema($this->mdlName);
      $colNames = $schema->getColumnNames();

      writeColumns($schema, $restore, $columns);

      foreach ($columns as $column) {
        if (in_array($column, $colNames)) {
          executeQuery("ALTER TABLE $tblName DROP COLUMN $column");
        } else {
          Sabel_Sakle_Task::warning("column '{$column}' of $tblName does not exist. (SKIP)");
        }
      }
    } else {
      $this->restoreDropColumn();
    }
  }

  protected function restoreDropColumn()
  {
    $this->execAddColumn(getAddColumns($this->getRestoreFileName()));
  }

  protected function getCreateSql($columns)
  {
    $query = array();

    foreach ($columns as $column) {
      $query[] = $this->createColumnAttributes($column);
    }

    if ($this->pkeys) {
      $query[] = "PRIMARY KEY(" . implode(", ", $this->pkeys) . ")";
    }

    if ($this->fkeys) {
      foreach ($this->fkeys as $fKey) {
        $query[] = $this->createForeignKey($fKey->get());
      }
    }

    if ($this->uniques) {
      foreach ($this->uniques as $unique) {
        $query[] = "UNIQUE (" . implode(", ", $unique) . ")";
      }
    }

    $tblName = convert_to_tablename($this->mdlName);
    return "CREATE TABLE $tblName (" . implode(", ", $query) . ")";
  }

  private function createForeignKey($object)
  {
    $query  = "FOREIGN KEY ({$object->column}) "
            . "REFERENCES {$object->refTable}({$object->refColumn})";

    if ($object->onDelete !== null) {
      $query .= " ON DELETE " . $object->onDelete;
    }

    if ($object->onUpdate !== null) {
      $query .= " ON UPDATE " . $object->onUpdate;
    }

    return $query;
  }

  protected function changeColumn()
  {
    $tblName = convert_to_tablename($this->mdlName);
    $restore = $this->getRestoreFileName();
    $schema  = getSchema($this->mdlName);

    if ($this->applyMode === "upgrade") {
      if (is_file($restore)) unlink($restore);

      $change = new Sabel_DB_Migration_Classes_ChangeColumn();
      eval (getPhpSource($this->filePath));

      $columns = $change->getChangeColumns();

      $names = array();
      foreach ($columns as $column) $names[] = $column->name;
      writeColumns($schema, $restore, $names, '$change');
      $this->changeColumnUpgrade($columns, $schema, $tblName);
    } else {
      $change = new Sabel_DB_Migration_Classes_ChangeColumn();
      eval (getPhpSource($restore));

      $columns = $change->getChangeColumns();
      $this->changeColumnDowngrade($columns, $schema, $tblName);
    }
  }

  protected function getRestoreFileName()
  {
    $dir = $this->dirPath . DIR_DIVIDER . "restores";
    if (!is_dir($dir)) mkdir($dir);

    return $dir . DIR_DIVIDER . "restore_" . $this->version;
  }

  protected function query()
  {
    $query = new Sabel_DB_Migration_Classes_Query();
    eval (getPhpSource($this->filePath));
    $query->execute();
  }

  protected function custom()
  {
    if ($this->applyMode === "upgrade") {
      $file = $this->filePath;
    } else {
      $file = $this->getRestoreFileName();
    }

    $mix  = new Sabel_DB_Migration_Classes_Custom();
    $mix->execute(get_class($this), $this->version, $file);
  }

  protected function getDefaultValue($column)
  {
    $d = $column->default;

    if ($column->isBool()) {
      return $this->getBooleanAttr($d);
    } elseif ($d === null || $d === _NULL) {
      return ($column->nullable === true) ? "DEFAULT NULL" : "";
    } elseif ($column->isNumeric()) {
      return "DEFAULT $d";
    } else {
      return "DEFAULT '{$d}'";
    }
  }
}
