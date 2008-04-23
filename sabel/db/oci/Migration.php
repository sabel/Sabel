<?php

/**
 * Sabel_DB_Oci_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Oci_Migration extends Sabel_DB_Abstract_Migration
{
  protected $types = array(Sabel_DB_Type::INT      => "NUMBER(10)",
                           Sabel_DB_Type::BIGINT   => "NUMBER(19)",
                           Sabel_DB_Type::SMALLINT => "NUMBER(5)",
                           Sabel_DB_Type::FLOAT    => "FLOAT(24)",
                           Sabel_DB_Type::DOUBLE   => "FLOAT(53)",
                           Sabel_DB_Type::BOOL     => "NUMBER(1)",
                           Sabel_DB_Type::STRING   => "VARCHAR2",
                           Sabel_DB_Type::TEXT     => "CLOB",
                           Sabel_DB_Type::DATETIME => "DATE",
                           Sabel_DB_Type::DATE     => "DATE",
                           Sabel_DB_Type::BINARY   => "BLOB");
  
  protected function create()
  {
    $tblName = convert_to_tablename($this->mdlName);
    $schema  = $this->getSchema();
    $tables  = $schema->getTableList();
    
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      if (in_array($tblName, $tables)) {
        Sabel_Console::warning("table '{$tblName}' already exists. (SKIP)");
      } else {
        $this->createTable($this->filePath);
      }
    } else {
      if (in_array($tblName, $tables)) {
        $this->dropSequence($schema->getTable($tblName)->getSequenceColumn());
        $this->executeQuery("DROP TABLE " . $this->quoteIdentifier($tblName));
      } else {
        Sabel_Console::warning("unknown table '{$tblName}'. (SKIP)");
      }
    }
  }
  
  protected function createTable($filePath)
  {
    $create = $this->getReader($filePath)->readCreate();
    $this->executeQuery($this->getCreateSql($create));
    
    foreach ($create->getColumns() as $column) {
      if ($column->increment) {
        $tblName = convert_to_tablename($this->mdlName);
        $seqName = strtoupper($tblName) . "_" . strtoupper($column->name) . "_SEQ";
        $this->executeQuery("CREATE SEQUENCE " . $seqName);
      } elseif ($column->isDate()) {
        $tblName = $this->quoteIdentifier(convert_to_tablename($this->mdlName));
        $colName = $this->quoteIdentifier($column->name);
        $this->executeQuery("COMMENT ON COLUMN {$tblName}.{$colName} IS 'date'");
      }
    }
  }
  
  protected function getCreateSql($create)
  {
    $query = array();
    $fkeys = $create->getForeignKeys();
    
    foreach ($create->getColumns() as $column) {
      $line = $this->createColumnAttributes($column);
      if (isset($fkeys[$column->name])) {
        $fkey  = $fkeys[$column->name]->get();
        $line .= " REFERENCES {$this->quoteIdentifier($fkey->refTable)}";
        $line .= "({$this->quoteIdentifier($fkey->refColumn)})";
        
        if ($fkey->onDelete !== null && $fkey->onDelete !== "NO ACTION") {
          $line .= " ON DELETE " . $fkey->onDelete;
        }
      }
      
      $query[] = $line;
    }
    
    if ($pkeys = $create->getPrimaryKeys()) {
      $cols = $this->quoteIdentifier($pkeys);
      $query[] = "PRIMARY KEY(" . implode(", ", $cols) . ")";
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
  
  protected function drop()
  {
    $restore = $this->getRestoreFileName();
    
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      if (is_file($restore)) unlink($restore);
      
      $schema = $this->getSchema()->getTable(convert_to_tablename($this->mdlName));
      $writer = new Sabel_DB_Migration_Writer($restore);
      $writer->writeTable($schema);
      $tblName = $this->quoteIdentifier($schema->getTableName());
      $this->executeQuery("DROP TABLE $tblName");
      $this->dropSequence($schema->getSequenceColumn());
    } else {
      $this->createTable($restore);
    }
  }
  
  private function dropSequence($incCol)
  {
    if ($incCol !== null) {
      $tblName = convert_to_tablename($this->mdlName);
      $seqName = strtoupper($tblName) . "_" . strtoupper($incCol) . "_SEQ";
      $this->executeQuery("DROP SEQUENCE " . $seqName);
    }
  }
  
  protected function changeColumnUpgrade($columns, $schema)
  {
    $tblName = $this->quoteIdentifier($schema->getTableName());
    
    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      $line = $this->alterChange($column, $current);
      $this->executeQuery("ALTER TABLE $tblName MODIFY $line");
    }
  }
  
  protected function changeColumnDowngrade($columns, $schema)
  {
    $tblName = $this->quoteIdentifier($schema->getTableName());
    
    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      $line = $this->alterChange($column, $current);
      $this->executeQuery("ALTER TABLE $tblName MODIFY $line");
    }
  }
  
  protected function createColumnAttributes($col)
  {
    $line   = array();
    $line[] = $this->quoteIdentifier($col->name);
    $line[] = $this->getTypeString($col);
    $line[] = $this->getDefaultValue($col);
    
    if (($nullable = $this->getNullableString($col)) !== "") {
      $line[] = $nullable;
    }
    
    return preg_replace("/[ ]{2,}/", " ", implode(" ", $line));
  }
  
  protected function alterChange($column, $current)
  {
    $line   = array();
    $line[] = $column->name;
    
    if ($current->isText() && $column->type !== null && !$column->isText()) {
      Sabel_Console::warning("cannot modify lob column '{$current->name}'. (SKIP)");
    } elseif (!$current->isText()) {
      $col  = ($column->type === null) ? $current : $column;
      $type = $this->getTypeString($col, false);
      
      if ($col->isString()) {
        $max = ($column->max === null) ? $current->max : $column->max;
        $line[] = $type . "({$max})";
      } else {
        $line[] = $type;
      }
    }
    
    if (($d = $column->default) === _NULL) {
      $line[] = "DEFAULT NULL";
    } else {
      $cd = $current->default;
      
      if ($d === $cd) {
        $line[] = $this->getDefaultValue($current);
      } else {
        $this->valueCheck($column, $d);
        $line[] = $this->getDefaultValue($column);
      }
    }
    
    if ($current->nullable === true && $column->nullable === false) {
      $line[] = "NOT NULL";
    } elseif ($current->nullable === false && $column->nullable === true) {
      $line[] = "NULL";
    }
    
    return implode(" ", $line);
  }
  
  private function getTypeString($col, $withLength = true)
  {
    if ($col->isString() && $withLength) {
      return $this->types[$col->type] . "({$col->max})";
    } else {
      return $this->types[$col->type];
    }
  }
  
  private function getNullableString($column)
  {
    return ($column->nullable === false) ? "NOT NULL" : "";
  }
  
  private function valueCheck($column, $default)
  {
    if ($default === null) return true;
    
    if (($column->isBool() && !is_bool($default)) ||
        ($column->isNumeric() && !is_numeric($default))) {
      throw new Sabel_DB_Exception("invalid default value.");
    } else {
      return true;
    }
  }
  
  protected function getBooleanAttr($value)
  {
    $v = ($value === true) ? "1" : "0";
    return "DEFAULT " . $v;
  }
}
