<?php

/**
 * Sabel_DB_Ibase_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Ibase_Migration extends Sabel_DB_Abstract_Migration
{
  protected $types = array(Sabel_DB_Type::INT      => "INTEGER",
                           Sabel_DB_Type::BIGINT   => "BIGINT",
                           Sabel_DB_Type::SMALLINT => "SMALLINT",
                           Sabel_DB_Type::FLOAT    => "FLOAT",
                           Sabel_DB_Type::DOUBLE   => "DOUBLE PRECISION",
                           Sabel_DB_Type::BOOL     => "CHAR(1)",
                           Sabel_DB_Type::STRING   => "VARCHAR",
                           Sabel_DB_Type::TEXT     => "BLOB SUB_TYPE TEXT",
                           Sabel_DB_Type::DATETIME => "TIMESTAMP",
                           Sabel_DB_Type::DATE     => "DATE");
                           
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
      }
    }
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
    throw new Sabel_Exception_Runtime("change column not supported.");
  }
  
  protected function changeColumnDowngrade($columns, $schema)
  {
    throw new Sabel_Exception_Runtime("change column not supported.");
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
    $v = ($value === true) ? "'1'" : "'0'";
    return "DEFAULT " . $v;
  }
}
