<?php

/**
 * Sabel_DB_Pdo_Sqlite_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Sqlite_Migration extends Sabel_DB_Abstract_Migration
{
  protected $types = array(Sabel_DB_Type::INT      => "int",
                           Sabel_DB_Type::BIGINT   => "bigint",
                           Sabel_DB_Type::SMALLINT => "smallint",
                           Sabel_DB_Type::FLOAT    => "float",
                           Sabel_DB_Type::DOUBLE   => "double",
                           Sabel_DB_Type::BOOL     => "boolean",
                           Sabel_DB_Type::STRING   => "varchar",
                           Sabel_DB_Type::TEXT     => "text",
                           Sabel_DB_Type::DATETIME => "datetime",
                           Sabel_DB_Type::DATE     => "date");
                           
  protected function createTable($filePath)
  {
    $create  = $this->getReader($filePath)->readCreate();
    $columns = $create->getColumns();
    $pkeys   = $create->getPrimaryKeys();
    $uniques = $create->getUniques();
    $query   = $this->makeCreateSql($columns, $pkeys, $uniques);
    
    $this->executeQuery($query);
  }
  
  protected function addColumn()
  {
    $columns = $this->getReader()->readAddColumn()->getColumns();
    
    if ($this->applyMode === "upgrade") {
      $this->execAddColumn($columns);
    } else {
      $tblName  = convert_to_tablename($this->mdlName);
      $schema   = $this->getSchema()->getTable($tblName);
      $currents = $schema->getColumns();
      
      foreach ($columns as $column) {
        $name = $column->name;
        if (isset($currents[$name])) unset($currents[$name]);
      }
      
      $this->dropColumnsAndRemakeTable($currents, $schema);
    }
  }
  
  protected function dropColumn()
  {
    $restore = $this->getRestoreFileName();
    
    if ($this->applyMode === "upgrade") {
      if (is_file($restore)) unlink($restore);
      
      $columns  = $this->getReader()->readDropColumn()->getColumns();
      $tblName  = convert_to_tablename($this->mdlName);
      $schema   = $this->getSchema()->getTable($tblName);
      $sColumns = $schema->getColumns();
      $colNames = $schema->getColumnNames();
      
      $writer = new Sabel_DB_Migration_Writer($restore);
      $writer->writeColumns($schema, $columns);
      $writer->close();
      
      foreach ($columns as $column) {
        if (isset($sColumns[$column])) {
          unset($sColumns[$column]);
        } else {
          $warning = "column '{$column}' does not exist. (SKIP)";
          Sabel_Console::warning($warning);
        }
      }
      
      $this->dropColumnsAndRemakeTable($sColumns, $schema);
    } else {
      $columns = $this->getReader($restore)->readAddColumn()->getColumns();
      $this->execAddColumn($columns);
    }
  }
  
  protected function changeColumnUpgrade($columns, $schema)
  {
    $sColumns = $schema->getColumns();
    
    foreach ($columns as $column) {
      if (isset($sColumns[$column->name])) {
        $column = $this->alterChange($column, $sColumns[$column->name]);
        $sColumns[$column->name] = $column;
      }
    }
    
    $this->dropColumnsAndRemakeTable($sColumns, $schema);
  }
  
  protected function changeColumnDowngrade($columns, $schema)
  {
    $sColumns = $schema->getColumns();
    
    foreach ($columns as $column) {
      if (isset($sColumns[$column->name])) $sColumns[$column->name] = $column;
    }
    
    $this->dropColumnsAndRemakeTable($sColumns, $schema);
  }
  
  protected function createColumnAttributes($col)
  {
    $line = array($this->quoteIdentifier($col->name));
    
    if ($col->increment) {
      $line[] = "integer PRIMARY KEY";
    } elseif ($col->isString()) {
      $line[] = $this->types[$col->type] . "({$col->max})";
    } else {
      $line[] = $this->types[$col->type];
    }
    
    if ($col->nullable === false) $line[] = "NOT NULL";
    $line[] = $this->getDefaultValue($col);
    
    return implode(" ", $line);
  }
  
  private function dropColumnsAndRemakeTable($columns, $schema)
  {
    $stmt    = $this->getStatement();
    $tblName = $schema->getTableName();
    $pkeys   = $schema->getPrimaryKey();
    $uniques = $schema->getUniques();
    $query   = $this->makeCreateSql($columns, $pkeys, $uniques);
    
    $quotedTblName = $stmt->quoteIdentifier($tblName);
    $tmpTblName = "sbl_tmp_{$tblName}";
    $query = str_replace(" TABLE $quotedTblName", " TABLE $tmpTblName", $query);
    
    $stmt->getDriver()->begin();
    $stmt->setQuery($query)->execute();
    
    $projection = array();
    foreach (array_keys($columns) as $key) $projection[] = $key;
    
    $projection = implode(", ", $stmt->quoteIdentifier($projection));
    $query = "INSERT INTO $tmpTblName SELECT $projection FROM $quotedTblName";
    
    $stmt->setQuery($query)->execute();
    $stmt->setQuery("DROP TABLE $quotedTblName")->execute();
    $stmt->setQuery("ALTER TABLE $tmpTblName RENAME TO $quotedTblName")->execute();
    $stmt->getDriver()->commit();
  }
  
  private function alterChange($column, $current)
  {
    if ($column->type === null) {
      $column->type = $current->type;
    }
    
    if ($column->isString() && $column->max === null) {
      $column->max = $current->max;
    }
    
    if ($column->nullable === null) {
      $column->nullable = $current->nullable;
    }
    
    if ($column->default === _NULL) {
      $column->default = null;
    } elseif ($column->default === null) {
      $column->default = $current->default;
    }
    
    return $column;
  }
  
  private function makeCreateSql($columns, $pkeys, $uniques)
  {
    $query  = array();
    $hasSeq = false;
    
    foreach ($columns as $column) {
      if ($column->increment) $hasSeq = true;
      $query[] = $this->createColumnAttributes($column);
    }
    
    if ($pkeys && !$hasSeq) {
      $cols = $this->quoteIdentifier($pkeys);
      $query[] = "PRIMARY KEY(" . implode(", ", $cols) . ")";
    }
    
    if ($uniques) {
      foreach ($uniques as $unique) {
        $cols = $this->quoteIdentifier($unique);
        $query[] = "UNIQUE (" . implode(", ", $cols) . ")";
      }
    }
    
    $tblName = $this->quoteIdentifier(convert_to_tablename($this->mdlName));
    return "CREATE TABLE $tblName (" . implode(", ", $query) . ")";
  }
  
  protected function getBooleanAttr($value)
  {
    $v = ($value === true) ? "true" : "false";
    return "DEFAULT " . $v;
  }
}
