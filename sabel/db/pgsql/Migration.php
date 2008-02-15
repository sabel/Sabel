<?php

/**
 * Sabel_DB_Pgsql_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Migration extends Sabel_DB_Abstract_Migration
{
  protected $types = array(Sabel_DB_Type::INT      => "INTEGER",
                           Sabel_DB_Type::BIGINT   => "BIGINT",
                           Sabel_DB_Type::SMALLINT => "SMALLINT",
                           Sabel_DB_Type::FLOAT    => "REAL",
                           Sabel_DB_Type::DOUBLE   => "DOUBLE PRECISION",
                           Sabel_DB_Type::BOOL     => "BOOLEAN",
                           Sabel_DB_Type::STRING   => "VARCHAR",
                           Sabel_DB_Type::TEXT     => "TEXT",
                           Sabel_DB_Type::DATETIME => "TIMESTAMP",
                           Sabel_DB_Type::DATE     => "DATE");
                           
  protected function createTable($filePath)
  {
    $query = $this->getCreateSql($this->getReader($filePath)->readCreate());
    $this->executeQuery($query);
  }
  
  protected function changeColumnUpgrade($columns, $schema)
  {
    $this->alterChange($columns, $schema);
  }
  
  protected function changeColumnDowngrade($columns, $schema)
  {
    $this->alterChange($columns, $schema);
  }
  
  protected function createColumnAttributes($column)
  {
    $line   = array();
    $line[] = $this->quoteIdentifier($column->name);
    $line[] = $this->getDataType($column);
    
    if ($column->nullable === false) $line[] = "NOT NULL";
    $line[] = $this->getDefaultValue($column);
    
    return implode(" ", $line);
  }
  
  private function alterChange($columns, $schema)
  {
    $tblName = $this->quoteIdentifier($schema->getTableName());
    $stmt = $this->getStatement();
    $stmt->getDriver()->begin();
    
    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      if ($column->type !== null || ($current->isString() && $column->max !== null)) {
        $this->changeType($current, $column, $tblName);
      }
      
      if ($column->nullable !== null) {
        $this->changeNullable($current, $column, $tblName);
      }
      
      if ($column->default !== $current->default) {
        $this->changeDefault($column, $tblName);
      }
    }
    
    $stmt->getDriver()->commit();
  }
  
  private function changeType($current, $column, $tblName)
  {
    $colName = $this->quoteIdentifier($column->name);
    
    if ($current->type !== $column->type && $column->type !== null) {
      $type = $this->getDataType($column);
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName TYPE $type");
    } elseif ($current->isString() && $current->max !== $column->max) {
      $column->type = $current->type;
      if ($column->max === null) $column->max = 255;
      $type = $this->getDataType($column);
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName TYPE $type");
    }
  }
  
  private function changeNullable($current, $column, $tblName)
  {
    if ($current->nullable === $column->nullable) return;
    $colName = $this->quoteIdentifier($column->name);
    
    if ($column->nullable) {
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName DROP NOT NULL");
    } else {
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName SET NOT NULL");
    }
  }
  
  private function changeDefault($column, $tblName)
  {
    $colName = $this->quoteIdentifier($column->name);
    
    if ($column->default === _NULL) {
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName DROP DEFAULT");
    } else {
      if ($column->isBool()) {
        $default = ($column->default) ? "true" : "false";
      } elseif ($column->isNumeric()) {
        $default = $column->default;
      } else {
        $default = "'{$column->default}'";
      }
      
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName SET DEFAULT $default");
    }
  }
  
  private function getDataType($col)
  {
    if ($col->increment) {
      if ($col->isInt()) {
        return "serial";
      } elseif ($col->isBigint()) {
        return "bigserial";
      } else {
        throw new Sabel_DB_Exception("invalid data type for sequence.");
      }
    } elseif ($col->isString()) {
      return $this->types[$col->type] . "({$col->max})";
    } else {
      return $this->types[$col->type];
    }
  }
  
  protected function getBooleanAttr($value)
  {
    $v = ($value === true) ? "true" : "false";
    return "DEFAULT " . $v;
  }
}
