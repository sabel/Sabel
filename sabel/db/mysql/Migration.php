<?php

/**
 * Sabel_DB_Mysql_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysql_Migration extends Sabel_DB_Abstract_Migration
{
  protected $types = array(Sabel_DB_Type::INT      => "INTEGER",
                           Sabel_DB_Type::BIGINT   => "BIGINT",
                           Sabel_DB_Type::SMALLINT => "SMALLINT",
                           Sabel_DB_Type::FLOAT    => "FLOAT",
                           Sabel_DB_Type::DOUBLE   => "DOUBLE",
                           Sabel_DB_Type::BOOL     => "TINYINT(1)",
                           Sabel_DB_Type::STRING   => "VARCHAR",
                           Sabel_DB_Type::TEXT     => "TEXT",
                           Sabel_DB_Type::DATETIME => "DATETIME",
                           Sabel_DB_Type::DATE     => "DATE");
                           
  protected function createTable($filePath)
  {
    $create  = $this->getReader($filePath)->readCreate();
    $query   = $this->getCreateSql($create);
    $options = $create->getOptions();
    
    if (isset($options["engine"])) {
      $query .= " ENGINE=" . $options["engine"];
    }
    
    $this->executeQuery($query);
    
    if ($indexes = $create->getIndexes()) {
      $this->createIndex($indexes);
    }
  }
  
  protected function dropIndex(array $idxColumns, $tblName = null)
  {
    if ($tblName === null) {
      $tblName = convert_to_tablename($this->mdlName);
    }
    
    $quotedTblName = $this->quoteIdentifier($tblName);
    foreach ($idxColumns as $colName) {
      $idxName = $tblName . "_" . $colName . "_idx";
      $this->executeQuery("DROP INDEX {$tblName}_{$colName}_idx ON $quotedTblName");
    }
  }
  
  public function drop()
  {
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      $restore = $this->getRestoreFileName();
      if (is_file($restore)) unlink($restore);
      
      $schema    = $this->getSchema();
      $tblName   = convert_to_tablename($this->mdlName);
      $tblSchema = $schema->getTable($tblName);
      $engine    = $schema->getTableEngine($tblName);
      
      $writer = new Sabel_DB_Migration_Writer($restore);
      $writer->writeTable($tblSchema);
      $writer->write('$create->options("engine", "' . $engine . '");');
      $writer->write(PHP_EOL);
      $writer->close();
      
      $this->executeQuery("DROP TABLE " . $this->quoteIdentifier($tblName));
    } else {
      $this->createTable($this->getRestoreFileName());
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
      $line = $this->createColumnAttributes($column);
      $this->executeQuery("ALTER TABLE $tblName MODIFY $line");
    }
  }
  
  protected function createColumnAttributes($col)
  {
    $line   = array();
    $line[] = $this->quoteIdentifier($col->name);
    $line[] = $this->getTypeString($col);
    $line[] = $this->getNullableString($col);
    $line[] = $this->getDefaultValue($col);
    
    if ($col->increment) $line[] = "AUTO_INCREMENT";
    return implode(" ", $line);
  }
  
  protected function alterChange($column, $current)
  {
    $line   = array();
    $line[] = $this->quoteIdentifier($column->name);
    
    $c = ($column->type === null) ? $current : $column;
    $line[] = $this->getTypeString($c, false);
    
    if ($c->isString()) {
      $max = ($column->max === null) ? $current->max : $column->max;
      $line[] = "({$max})";
    }
    
    $c = ($column->nullable === null) ? $current : $column;
    $line[] = $this->getNullableString($c);
    
    if (($d = $column->default) !== _NULL) {
      $cd = $current->default;
      
      if ($d === $cd) {
        $line[] = $this->getDefaultValue($current);
      } else {
        $this->valueCheck($column, $d);
        $line[] = $this->getDefaultValue($column);
      }
    }
    
    if ($column->increment) $line[] = "AUTO_INCREMENT";
    return implode(" ", $line);
  }
  
  private function getTypeString($col, $withLength = true)
  {
    if (!$withLength) return $this->types[$col->type];
    
    if ($col->isString()) {
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
  
  protected function getDefaultValue($column)
  {
    $d = $column->default;
    
    if ($column->isBool()) {
      return $this->getBooleanAttr($d);
    } elseif ($d === null || $d === _NULL) {
      if ($column->isString()) {
        return ($column->nullable === true) ? "DEFAULT ''" : "";
      } else {
        return ($column->nullable === true) ? "DEFAULT NULL" : "";
      }
    } elseif ($column->isNumeric()) {
      return "DEFAULT $d";
    } else {
      return "DEFAULT '{$d}'";
    }
  }
  
  protected function getBooleanAttr($value)
  {
    $value = ($value === true) ? "1" : "0";
    return "DEFAULT " . $value;
  }
}
