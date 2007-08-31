<?php

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
abstract class Sabel_DB_Abstract_Migration
{
  protected
    $applyMode = "",
    $filePath  = "",
    $mdlName   = "",
    $command   = "",
    $version   = 0;

  abstract protected function getBooleanAttr($value);

  public function __construct($filePath, $applyMode)
  {
    $this->filePath  = $filePath;
    $this->applyMode = $applyMode;

    $file = basename($filePath);
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

  protected function create()
  {
    $tblName  = convert_to_tablename($this->mdlName);
    $accessor = Sabel_DB_Migration_Manager::getAccessor();
    $tables   = $accessor->getTableList();

    if ($this->applyMode === "upgrade") {
      if (in_array($tblName, $tables)) {
        Sabel_Sakle_Task::warning("table '{$tblName}' already exists. (SKIP)");
      } else {
        $this->createTable($this->filePath);
      }
    } else {
      if (in_array($tblName, $tables)) {
        $driver = Sabel_DB_Migration_Manager::getDriver();
        $driver->execute("DROP TABLE " . $tblName);
      } else {
        Sabel_Sakle_Task::warning("unknown table '{$tblName}'. (SKIP)");
      }
    }
  }

  protected function drop()
  {
    $restore = $this->getRestoreFileName();

    if ($this->applyMode === "upgrade") {
      if (is_file($restore)) unlink($restore);
      $driver   = Sabel_DB_Migration_Manager::getDriver();
      $accessor = Sabel_DB_Migration_Manager::getAccessor();
      $schema   = $accessor->get(convert_to_tablename($this->mdlName));
      $writer   = new Sabel_DB_Migration_Writer($restore);
      $writer->writeTable($schema);
      $driver->execute("DROP TABLE " . $schema->getTableName());
    } else {
      $this->createTable($restore);
    }
  }

  protected function addColumn()
  {
    $reader  = new Sabel_DB_Migration_Reader($this->filePath);
    $columns = $reader->readAddColumn()->getColumns();

    if ($this->applyMode === "upgrade") {
      $this->execAddColumn($columns);
    } else {
      $tblName = convert_to_tablename($this->mdlName);
      $driver  = Sabel_DB_Migration_Manager::getDriver();
      foreach ($columns as $column) {
        $driver->execute("ALTER TABLE $tblName DROP COLUMN " . $column->name);
      }
    }
  }

  protected function execAddColumn($columns)
  {
    $tblName  = convert_to_tablename($this->mdlName);
    $accessor = Sabel_DB_Migration_Manager::getAccessor();
    $names    = $accessor->getColumnNames(convert_to_tablename($this->mdlName));

    foreach ($columns as $column) {
      if (in_array($column->name, $names)) {
        Sabel_Sakle_Task::warning("duplicate column '{$column->name}'. (SKIP)");
      } else {
        $line = $this->createColumnAttributes($column);
        $driver = Sabel_DB_Migration_Manager::getDriver();
        $driver->execute("ALTER TABLE $tblName ADD " . $line);
      }
    }
  }

  protected function dropColumn()
  {
    $restore = $this->getRestoreFileName();

    if ($this->applyMode === "upgrade") {
      if (is_file($restore)) unlink($restore);
      $driver   = Sabel_DB_Migration_Manager::getDriver();
      $accessor = Sabel_DB_Migration_Manager::getAccessor();
      $reader   = new Sabel_DB_Migration_Reader($this->filePath);
      $columns  = $reader->readDropColumn()->getColumns();
      $schema   = $accessor->get(convert_to_tablename($this->mdlName));
      $tblName  = $schema->getTableName();
      $colNames = $schema->getColumnNames();

      $writer = new Sabel_DB_Migration_Writer($restore);
      $writer->writeColumns($schema, $columns);
      $writer->close();

      foreach ($columns as $column) {
        if (in_array($column, $colNames)) {
          $driver->execute("ALTER TABLE $tblName DROP COLUMN $column");
        } else {
          Sabel_Sakle_Task::warning("column '{$column}' does not exist. (SKIP)");
        }
      }
    } else {
      $reader  = new Sabel_DB_Migration_Reader($restore);
      $columns = $reader->readAddColumn()->getColumns();
      $this->execAddColumn($columns);
    }
  }

  protected function changeColumn()
  {
    $accessor = Sabel_DB_Migration_Manager::getAccessor();
    $schema   = $accessor->get(convert_to_tablename($this->mdlName));
    $tblName  = $schema->getTableName();
    $restore  = $this->getRestoreFileName();

    if ($this->applyMode === "upgrade") {
      if (is_file($restore)) unlink($restore);

      $reader  = new Sabel_DB_Migration_Reader($this->filePath);
      $columns = $reader->readChangeColumn()->getColumns();

      $names = array();
      foreach ($columns as $column) $names[] = $column->name;

      $writer = new Sabel_DB_Migration_Writer($restore);
      $writer->writeColumns($schema, $names, '$change');
      $writer->close();

      $this->changeColumnUpgrade($columns, $schema);
    } else {
      $reader  = new Sabel_DB_Migration_Reader($restore);
      $columns = $reader->readChangeColumn()->getColumns();
      $this->changeColumnDowngrade($columns, $schema);
    }
  }

  protected function getCreateSql($create)
  {
    $query   = array();
    $columns = $create->getColumns();
    $pkeys   = $create->getPrimaryKeys();
    $fkeys   = $create->getForeignKeys();
    $uniques = $create->getUniques();

    foreach ($columns as $column) {
      $query[] = $this->createColumnAttributes($column);
    }

    if ($pkeys) {
      $query[] = "PRIMARY KEY(" . implode(", ", $pkeys) . ")";
    }

    if ($fkeys) {
      foreach ($fkeys as $fkey) {
        $query[] = $this->createForeignKey($fkey->get());
      }
    }

    if ($uniques) {
      foreach ($uniques as $unique) {
        $query[] = "UNIQUE (" . implode(", ", $unique) . ")";
      }
    }

    $tblName = convert_to_tablename($this->mdlName);
    return "CREATE TABLE $tblName (" . implode(", ", $query) . ")";
  }

  protected function createForeignKey($object)
  {
    $query = "FOREIGN KEY ({$object->column}) "
           . "REFERENCES {$object->refTable}({$object->refColumn})";

    if ($object->onDelete !== null) {
      $query .= " ON DELETE " . $object->onDelete;
    }

    if ($object->onUpdate !== null) {
      $query .= " ON UPDATE " . $object->onUpdate;
    }

    return $query;
  }

  protected function getRestoreFileName()
  {
    $directory = Sabel_DB_Migration_Manager::getDirectory();
    $dir = $directory . DS . "restores";
    if (!is_dir($dir)) mkdir($dir);

    return $dir . DS . "restore_" . $this->version;
  }

  protected function query()
  {
    $reader = new Sabel_DB_Migration_Reader($this->filePath);
    $reader->readQuery()->execute();
  }

  protected function custom()
  {
    if ($this->applyMode === "upgrade") {
      $file = $this->filePath;
    } else {
      $file = $this->getRestoreFileName();
    }

    $custom = new Sabel_DB_Migration_Custom();
    $custom->execute(get_class($this), $this->version, $file);
  }

  protected function getDefaultValue($column)
  {
    $d = $column->default;

    if ($column->isBool()) {
      return $this->getBooleanAttr($d);
    } elseif ($d === null || $d === _NULL) {
      return ($column->nullable === true) ? "DEFAULT NULL" : "";
    } elseif ($column->isBigint()) {
      return "DEFAULT '{$d}'";
    } elseif ($column->isNumeric()) {
      return "DEFAULT $d";
    } else {
      return "DEFAULT '{$d}'";
    }
  }
}
