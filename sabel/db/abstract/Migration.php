<?php

/**
 * Sabel_DB_Abstract_Migration
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Migration extends Sabel_Object
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
    if ($this->hasMethod($command)) {
      $this->$command();
    } else {
      throw new Sabel_DB_Exception("command '$command' not found.");
    }
  }
  
  protected function create()
  {
    $tblName = convert_to_tablename($this->mdlName);
    $tables  = $this->getSchema()->getTableList();
    
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      if (in_array($tblName, $tables)) {
        Sabel_Command::warning("table '{$tblName}' already exists. (SKIP)");
      } else {
        $this->createTable($this->filePath);
      }
    } else {
      if (in_array($tblName, $tables)) {
        $tblName = $this->quoteIdentifier($tblName);
        $this->executeQuery("DROP TABLE $tblName");
      } else {
        Sabel_Command::warning("unknown table '{$tblName}'. (SKIP)");
      }
    }
  }
  
  protected function drop()
  {
    $restore = $this->getRestoreFileName();
    
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      if (is_file($restore)) unlink($restore);
      $tblName = convert_to_tablename($this->mdlName);
      $schema  = $this->getSchema()->getTable($tblName);
      $writer  = new Sabel_DB_Migration_Writer($restore);
      $writer->writeTable($schema);
      $this->executeQuery("DROP TABLE " . $this->quoteIdentifier($tblName));
    } else {
      $this->createTable($restore);
    }
  }
  
  protected function addColumn()
  {
    $columns = $this->getReader()->readAddColumn()->getColumns();
    
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      $this->execAddColumn($columns);
    } else {
      $tblName = $this->quoteIdentifier(convert_to_tablename($this->mdlName));
      foreach ($columns as $column) {
        $colName = $this->quoteIdentifier($column->name);
        $this->executeQuery("ALTER TABLE $tblName DROP COLUMN $colName");
      }
    }
  }
  
  protected function execAddColumn($columns)
  {
    $tblName = convert_to_tablename($this->mdlName);
    $quotedTblName = $this->quoteIdentifier($tblName);
    $names = $this->getSchema()->getTable($tblName)->getColumnNames();
    
    foreach ($columns as $column) {
      if (in_array($column->name, $names)) {
        Sabel_Command::warning("duplicate column '{$column->name}'. (SKIP)");
      } else {
        $line = $this->createColumnAttributes($column);
        $this->executeQuery("ALTER TABLE $quotedTblName ADD $line");
      }
    }
  }
  
  protected function dropColumn()
  {
    $restore = $this->getRestoreFileName();
    
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      if (is_file($restore)) unlink($restore);
      
      $columns  = $this->getReader()->readDropColumn()->getColumns();
      $tblName  = convert_to_tablename($this->mdlName);
      $schema   = $this->getSchema()->getTable($tblName);
      $colNames = $schema->getColumnNames();
      
      $writer = new Sabel_DB_Migration_Writer($restore);
      $writer->writeColumns($schema, $columns);
      $writer->close();
      
      $quotedTblName = $this->quoteIdentifier($tblName);
      
      foreach ($columns as $column) {
        if (in_array($column, $colNames)) {
          $colName = $this->quoteIdentifier($column);
          $this->executeQuery("ALTER TABLE $quotedTblName DROP COLUMN $colName");
        } else {
          Sabel_Command::warning("column '{$column}' does not exist. (SKIP)");
        }
      }
    } else {
      $columns = $this->getReader($restore)->readAddColumn()->getColumns();
      $this->execAddColumn($columns);
    }
  }
  
  protected function changeColumn()
  {
    $tblName = convert_to_tablename($this->mdlName);
    $schema  = $this->getSchema()->getTable($tblName);
    $restore = $this->getRestoreFileName();
    
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      if (is_file($restore)) unlink($restore);
      
      $names = array();
      $columns = $this->getReader()->readChangeColumn()->getColumns();
      foreach ($columns as $column) $names[] = $column->name;
      
      $writer = new Sabel_DB_Migration_Writer($restore);
      $writer->writeColumns($schema, $names, '$change');
      $writer->close();
      
      $this->changeColumnUpgrade($columns, $schema);
    } else {
      $columns = $this->getReader($restore)->readChangeColumn()->getColumns();
      $this->changeColumnDowngrade($columns, $schema);
    }
  }
  
  protected function getCreateSql($create)
  {
    $query = array();
    foreach ($create->getColumns() as $column) {
      $query[] = $this->createColumnAttributes($column);
    }
    
    if ($pkeys = $create->getPrimaryKeys()) {
      $cols = $this->quoteIdentifier($pkeys);
      $query[] = "PRIMARY KEY(" . implode(", ", $cols) . ")";
    }
    
    if ($fkeys = $create->getForeignKeys()) {
      foreach ($fkeys as $fkey) {
        $query[] = $this->createForeignKey($fkey->get());
      }
    }
    
    if ($uniques = $create->getUniques()) {
      foreach ($uniques as $unique) {
        $cols = $this->quoteIdentifier($unique);
        $query[] = "UNIQUE (" . implode(", ", $cols) . ")";
      }
    }
    
    $tblName = $this->quoteIdentifier(convert_to_tablename($this->mdlName));
    return "CREATE TABLE $tblName (" . implode(", ", $query) . ")";
  }
  
  protected function createForeignKey($object)
  {
    $query = "FOREIGN KEY ({$this->quoteIdentifier($object->column)}) "
           . "REFERENCES {$this->quoteIdentifier($object->refTable)}"
           . "({$this->quoteIdentifier($object->refColumn)})";
           
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
    
    return $dir . DS . "restore_" . $this->version . PHP_SUFFIX;
  }
  
  public function index()
  {
    $index = $this->getReader()->readIndex();
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      if ($createIndexes = $index->getCreateIndexes()) {
        $this->createIndex($createIndexes);
      }
      if ($dropIndexes = $index->getDropIndexes()) {
        $this->dropIndex($dropIndexes);
      }
    } else {
      if ($createIndexes = $index->getCreateIndexes()) {
        $this->dropIndex($createIndexes);
      }
      if ($dropIndexes = $index->getDropIndexes()) {
        $this->createIndex($dropIndexes);
      }
    }
  }
  
  protected function createIndex(array $idxColumns, $tblName = null)
  {
    if ($tblName === null) {
      $tblName = convert_to_tablename($this->mdlName);
    }
    
    $quotedTblName = $this->quoteIdentifier($tblName);
    foreach ($idxColumns as $colName) {
      $idxName = $tblName . "_" . $colName . "_idx";
      $colName = $this->quoteIdentifier($colName);
      $this->executeQuery("CREATE INDEX $idxName ON {$quotedTblName}({$colName})");
    }
  }
  
  protected function dropIndex(array $idxColumns, $tblName = null)
  {
    if ($tblName === null) {
      $tblName = convert_to_tablename($this->mdlName);
    }
    
    foreach ($idxColumns as $colName) {
      $idxName = $tblName . "_" . $colName . "_idx";
      $this->executeQuery("DROP INDEX {$tblName}_{$colName}_idx");
    }
  }
  
  protected function query()
  {
    $this->getReader()->readQuery()->execute();
  }
  
  protected function custom()
  {
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      $file = $this->filePath;
    } else {
      $file = $this->getRestoreFileName();
    }
    
    $custom = new Sabel_DB_Migration_Custom();
    $custom->execute(get_class($this), $this->version, $file);
  }
  
  protected function getReader($filePath = null)
  {
    if ($filePath === null) $filePath = $this->filePath;
    return new Sabel_DB_Migration_Reader($filePath);
  }
  
  protected function getSchema()
  {
    return Sabel_DB_Migration_Manager::getSchema();
  }
  
  protected function getStatement()
  {
    return Sabel_DB_Migration_Manager::getStatement();
  }
  
  protected function executeQuery($query)
  {
    return $this->getStatement()->setQuery($query)->execute();
  }
  
  protected function quoteIdentifier($arg)
  {
    return $this->getStatement()->quoteIdentifier($arg);
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
